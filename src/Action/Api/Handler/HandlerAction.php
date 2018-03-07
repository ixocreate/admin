<?php
namespace KiwiSuite\Admin\Action\Handler;

use KiwiSuite\Admin\Entity\User;
use KiwiSuite\Admin\Message\Crud\CreateMessage;
use KiwiSuite\Admin\Message\Crud\DeleteMessage;
use KiwiSuite\Admin\Message\Crud\UpdateMessage;
use KiwiSuite\Admin\Resource\ResourceInterface;
use KiwiSuite\Admin\Resource\ResourceSubManager;
use KiwiSuite\Admin\Response\ApiErrorResponse;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\CommandBus\CommandBus;
use KiwiSuite\CommandBus\Message\MessageInterface;
use KiwiSuite\CommandBus\Message\MessageSubManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Router\RouteResult;

final class HandlerAction implements MiddlewareInterface
{

    /**
     * @var MessageSubManager
     */
    private $messageSubManager;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var ResourceSubManager
     */
    private $resourceSubManager;

    public function __construct(
        MessageSubManager $messageSubManager,
        CommandBus $commandBus,
        ResourceSubManager $resourceSubManager
    ) {
        $this->messageSubManager = $messageSubManager;
        $this->commandBus = $commandBus;
        $this->resourceSubManager = $resourceSubManager;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var RouteResult $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);
        $options = $routeResult->getMatchedRoute()->getOptions();
        if (empty($options[MessageInterface::class])) {
            throw new \Exception("invalid message");
        }

        $messageClass = $options[MessageInterface::class];
        if (!empty($routeResult->getMatchedRoute()->getOptions()[ResourceInterface::class])) {
            $resourceKey = $routeResult->getMatchedRoute()->getOptions()[ResourceInterface::class];

            /** @var ResourceInterface $resource */
            $resource = $this->resourceSubManager->get($resourceKey);
            switch ($messageClass) {
                case UpdateMessage::class:
                    $messageClass = $resource->updateMessage() ?? $messageClass;
                    break;
                case CreateMessage::class:
                    $messageClass = $resource->createMessage() ?? $messageClass;
                    break;
                case DeleteMessage::class:
                    $messageClass = $resource->deleteMessage() ?? $messageClass;
                    break;
            }
        }

        /** @var MessageInterface $message */
        $message = $this->messageSubManager->build($messageClass);

        $body = $request->getParsedBody();
        if (empty($body)) {
            $body = [];
        }

        $metadata = $routeResult->getMatchedParams();
        if (empty($metadata)) {
            $metadata = [];
        }

        $metadata[User::class] = $request->getAttribute(User::class, null);
        if (!empty($metadata[User::class])) {
            $metadata[User::class] = $metadata[User::class]->id();
        }

        if (!empty($routeResult->getMatchedRoute()->getOptions()[ResourceInterface::class])) {
            $metadata[ResourceInterface::class] = $routeResult->getMatchedRoute()->getOptions()[ResourceInterface::class];
            $metadata['id'] = $request->getAttribute('id', null);
        }

        $message = $message->inject($body, $metadata);

        $result = $message->validate();
        if (!$result->isSuccessful()) {
            return new ApiErrorResponse('invalid.input', $result->getErrors());
        }

        $this->commandBus->handle($message);

        return new ApiSuccessResponse();
    }
}
