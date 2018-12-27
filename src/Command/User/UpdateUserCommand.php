<?php
declare(strict_types=1);

namespace Ixocreate\Admin\Command\User;


use Ixocreate\Admin\Config\AdminConfig;
use Ixocreate\Admin\Event\UserEvent;
use Ixocreate\Admin\Repository\UserRepository;
use Ixocreate\CommandBus\Command\AbstractCommand;
use Ixocreate\CommonTypes\Entity\SchemaType;
use Ixocreate\Contract\Schema\AdditionalSchemaInterface;
use Ixocreate\Entity\Type\Type;
use Ixocreate\Event\EventDispatcher;
use Ixocreate\Schema\AdditionalSchema\AdditionalSchemaSubManager;

class UpdateUserCommand extends AbstractCommand
{

    /**
     * @var AdminConfig
     */
    private $adminConfig;
    /**
     * @var AdditionalSchemaSubManager
     */
    private $additionalSchemaSubManager;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * UpdateUserCommand constructor.
     * @param AdminConfig $adminConfig
     * @param AdditionalSchemaSubManager $additionalSchemaSubManager
     * @param UserRepository $userRepository
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(
        AdminConfig $adminConfig,
        AdditionalSchemaSubManager $additionalSchemaSubManager,
        UserRepository $userRepository,
        EventDispatcher $eventDispatcher
    )
    {
        $this->adminConfig = $adminConfig;
        $this->additionalSchemaSubManager = $additionalSchemaSubManager;
        $this->userRepository = $userRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function execute(): bool
    {
        $data = $this->data();
        $entity = $this->userRepository->find($data['userId']);

        foreach ($data as $name => $value) {
            if ($name === 'userId') {
                continue;
            }
            $entity = $entity->with($name, $value);
        }

        $additionalSchema = $this->receiveAdditionalSchema();

        if ($additionalSchema !== null) {

            $content = [
                '__receiver__' => [
                    'receiver' => AdditionalSchemaSubManager::class,
                    'options' => [
                        'additionalSchema' => $additionalSchema::serviceName()
                    ]
                ],
                '__value__' => $data,
            ];

            $type = (Type::create($content, SchemaType::class))->convertToDatabaseValue();

            $entity = $entity->with('userAttributes', $type);
        }

        $entity = $entity->with('updatedAt', new \DateTimeImmutable());
        $this->userRepository->save($entity);

        $this->eventDispatcher->dispatch(UserEvent::EVENT_UPDATE, new UserEvent($entity));

        return true;

    }

    public static function serviceName(): string
    {
        return 'admin.user-update';
    }


    /**
     * @return AdditionalSchemaInterface|null
     */
    private function receiveAdditionalSchema(): ?AdditionalSchemaInterface
    {
        $schema = null;

        if (!empty($this->adminConfig->userAttributesSchema())) {
            $schema = $this->additionalSchemaSubManager->get($this->adminConfig->userAttributesSchema());
        }

        return $schema;
    }


}
