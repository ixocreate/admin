<?php
declare(strict_types=1);

namespace KiwiSuite\Admin\Action\Resource\Widgets;

use KiwiSuite\Admin\Dashboard\DashboardWidgetCollector;
use KiwiSuite\Admin\Dashboard\DashboardWidgetProviderSubManager;
use KiwiSuite\Admin\Entity\User;
use KiwiSuite\Admin\Repository\UserRepository;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Contract\Resource\AdminAwareInterface;
use KiwiSuite\Contract\Resource\ResourceInterface;
use KiwiSuite\Resource\SubManager\ResourceSubManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Router\RouteResult;
use Zend\Stratigility\MiddlewarePipe;
use KiwiSuite\Contract\Resource\Widgets\WidgetsInterface;

class WidgetsAction implements MiddlewareInterface
{
    /**
     * @var ResourceSubManager
     */
    private $resourceSubManager;

    /**
     * @var DashboardWidgetProviderSubManager
     */
    private $dashboardWidgetSubManager;

    private $userRepository;

    /**
     * WidgetsAction constructor.
     * @param ResourceSubManager $resourceSubManager
     * @param DashboardWidgetProviderSubManager $dashboardWidgetSubManager
     */
    public function __construct(ResourceSubManager $resourceSubManager, DashboardWidgetProviderSubManager $dashboardWidgetSubManager, UserRepository $userRepository)
    {
        $this->resourceSubManager = $resourceSubManager;
        $this->dashboardWidgetSubManager = $dashboardWidgetSubManager;
        $this->userRepository = $userRepository;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $collector = new DashboardWidgetCollector();

        $user = $request->getAttribute(User::class);

        /** @var WidgetsInterface $resource */
        $resource = $this->resourceSubManager->get($request->getAttribute('resource'));

        /** @var RouteResult $route */
        $route = $request->getAttribute(RouteResult::class);
        $path = ($route->getMatchedRoute())->getPath();

        $explode = explode('/', $path);

        $position = $explode[3];
        $type = $explode[4];

        switch ($type) {
            case 'list':
                if ($position === 'above') {
                    $resource->receiveAboveListWidgets($user, $collector);
                }
                if ($position === 'below') {
                    $resource->receiveBelowListWidgets($user, $collector);
                }
                break;
            case 'create':
                if ($position === 'above') {
                    $resource->receiveAboveCreateWidgets($user, $collector);
                }
                if ($position === 'below') {
                    $resource->receiveBelowCreateWidgets($user, $collector);
                }
                break;
            case 'edit':
                if ($position === 'above') {
                    $resource->receiveAboveEditWidgets($user, $collector);
                }
                if ($position === 'below') {
                    $resource->receiveBelowEditWidgets($user, $collector);
                }
                break;
        }

        return new ApiSuccessResponse(['items' => $collector->widgets()]);
    }
}