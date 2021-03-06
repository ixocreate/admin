<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Admin;

use Ixocreate\Admin\Config\AdminProjectConfig;
use Ixocreate\Admin\Config\Client\ClientConfigProviderSubManager;
use Ixocreate\Admin\Config\Navigation\Group;
use Ixocreate\Admin\Permission\Voter\VoterInterface;
use Ixocreate\Admin\Permission\Voter\VoterSubManager;
use Ixocreate\Admin\Role\RoleSubManager;
use Ixocreate\Admin\Schema\User\LocaleAttributesSchema;
use Ixocreate\Admin\Widget\DashboardWidgetProviderInterface;
use Ixocreate\Admin\Widget\DashboardWidgetProviderSubManager;
use Ixocreate\Application\Configurator\ConfiguratorInterface;
use Ixocreate\Application\Service\ServiceRegistryInterface;
use Ixocreate\Application\ServiceManager\SubManagerConfigurator;
use Ixocreate\Schema\AdditionalSchemaInterface;
use Ixocreate\Schema\SchemaSubManager;
use Ixocreate\ServiceManager\Factory\AutowireFactory;
use Laminas\Stdlib\SplPriorityQueue;

final class AdminConfigurator implements ConfiguratorInterface
{
    private $config = [
        'secret' => '',
        'author' => '',
        'copyright' => '',
        'description' => '',
        'name' => '',
        'poweredBy' => true,
        'logo' => [
            'image' => '',
            'width' => 0,
            'height' => 0,
        ],
        'loginLogo' => '',
        'icon' => '',
        'background' => '',
        'loginMessage' => '',
        'clientConfigProvider' => [],
        'adminBuildPath' => __DIR__ . '/../../admin-frontend/build/',
        'userAttributesSchema' => null,
        'accountAttributesSchema' => null,
        'localeAttributesSchema' => LocaleAttributesSchema::class,
        'defaultLocale' => 'en_US',
        'defaultTimezone' => 'UTC',
        'googleMapApiKey' => null,
        'sessionTimeout' => 7200,
        'uri' => '/admin',
    ];

    /**
     * @var Group[]
     */
    private $navigation = [];

    /**
     * @var SubManagerConfigurator
     */
    private $clientSubManagerConfigurator;

    /**
     * @var SubManagerConfigurator
     */
    private $roleSubManagerConfigurator;

    /**
     * @var SubManagerConfigurator
     */
    private $dashboardWidgetSubManagerConfigurator;

    /**
     * @var SubManagerConfigurator
     */
    private $additionalSchemaSubManagerConfigurator;

    /**
     * @var SubManagerConfigurator
     */
    private $voterSubManagerConfigurator;

    /**
     * AdminConfigurator constructor.
     */
    public function __construct()
    {
        $this->clientSubManagerConfigurator = new SubManagerConfigurator(
            ClientConfigProviderSubManager::class,
            ClientConfigProviderInterface::class
        );
        $this->roleSubManagerConfigurator = new SubManagerConfigurator(
            RoleSubManager::class,
            \Ixocreate\Admin\RoleInterface::class
        );
        $this->dashboardWidgetSubManagerConfigurator = new SubManagerConfigurator(
            DashboardWidgetProviderSubManager::class,
            DashboardWidgetProviderInterface::class
        );
        $this->additionalSchemaSubManagerConfigurator = new SubManagerConfigurator(
            SchemaSubManager::class,
            AdditionalSchemaInterface::class
        );
        $this->voterSubManagerConfigurator = new SubManagerConfigurator(
            VoterSubManager::class,
            VoterInterface::class
        );

        $this->addLocaleAttributesSchema(LocaleAttributesSchema::class);
    }

    /**
     * @param string $secret
     */
    public function setSecret(string $secret): void
    {
        $this->config['secret'] = $secret;
    }

    /**
     * @param string $author
     */
    public function setAuthor(string $author): void
    {
        $this->config['author'] = $author;
    }

    /**
     * @param string $copyright
     */
    public function setCopyright(string $copyright): void
    {
        $this->config['copyright'] = $copyright;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->config['description'] = $description;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->config['name'] = $name;
    }

    /**
     * @param bool $poweredBy
     */
    public function setPoweredBy(bool $poweredBy): void
    {
        $this->config['poweredBy'] = $poweredBy;
    }

    /**
     * @param string $logo
     * @param int $width
     * @param int $height
     */
    public function setLogo(string $logo, int $width, int $height): void
    {
        $this->config['logo'] = [
            'image' => $logo,
            'width' => $width,
            'height' => $height,
        ];
    }

    /**
     * @param string $logo
     */
    public function setLoginLogo(string $logo): void
    {
        $this->config['loginLogo'] = $logo;
    }

    /**
     * @param string $icon
     */
    public function setIcon(string $icon): void
    {
        $this->config['icon'] = $icon;
    }

    /**
     * @param string $background
     */
    public function setBackground(string $background): void
    {
        $this->config['background'] = $background;
    }

    /**
     * @param string $buildPath
     */
    public function setAdminBuildPath(string $buildPath): void
    {
        $this->config['adminBuildPath'] = $buildPath;
    }

    /**
     * @param string $googleMapApiKey
     */
    public function setGoogleMapApiKey(string $googleMapApiKey): void
    {
        $this->config['googleMapApiKey'] = $googleMapApiKey;
    }

    /**
     * @param string $userAttributesSchema
     * @param string $factory
     */
    public function addUserAttributesSchema(
        string $userAttributesSchema,
        string $factory = AutowireFactory::class
    ): void {
        $this->config['userAttributesSchema'] = $userAttributesSchema;
        $this->additionalSchemaSubManagerConfigurator->addFactory($userAttributesSchema, $factory);
    }

    /**
     * @param string $accountAttributesSchema
     * @param string $factory
     */
    public function addAccountAttributesSchema(
        string $accountAttributesSchema,
        string $factory = AutowireFactory::class
    ): void {
        $this->config['accountAttributesSchema'] = $accountAttributesSchema;
        $this->additionalSchemaSubManagerConfigurator->addFactory($accountAttributesSchema, $factory);
    }

    /**
     * @param string $localeAttributesSchema
     * @param string $factory
     */
    public function addLocaleAttributesSchema(
        string $localeAttributesSchema,
        string $factory = AutowireFactory::class
    ): void {
        $this->config['localeAttributesSchema'] = $localeAttributesSchema;
        $this->additionalSchemaSubManagerConfigurator->addFactory($localeAttributesSchema, $factory);
    }

    /**
     * @param string $locale
     */
    public function setDefaultLocale(string $locale): void
    {
        $this->config['defaultLocale'] = $locale;
    }

    /**
     * @param string $timezone
     */
    public function setDefaultTimezone(string $timezone)
    {
        $this->config['defaultTimezone'] = $timezone;
    }

    /**
     * @param string $clientProvider
     * @param string $factory
     */
    public function addClientProvider(string $clientProvider, string $factory = AutowireFactory::class): void
    {
        $this->config['clientConfigProvider'][] = $clientProvider;
        $this->clientSubManagerConfigurator->addFactory($clientProvider, $factory);
    }

    /**
     * @param string $directory
     * @param bool $recursive
     */
    public function addRoleDirectory(string $directory, bool $recursive = true): void
    {
        $this->roleSubManagerConfigurator->addDirectory($directory, $recursive);
    }

    /**
     * @param string $role
     * @param string $factory
     */
    public function addRole(string $role, string $factory = AutowireFactory::class): void
    {
        $this->roleSubManagerConfigurator->addFactory($role, $factory);
    }

    /**
     * @param string $provider
     * @param string $factory
     */
    public function addDashboardProvider(string $provider, string $factory = AutowireFactory::class): void
    {
        $this->dashboardWidgetSubManagerConfigurator->addFactory($provider, $factory);
    }

    /**
     * @param string $directory
     * @param bool $recursive
     */
    public function addDashboardProviderDirectory(string $directory, bool $recursive = true): void
    {
        $this->dashboardWidgetSubManagerConfigurator->addDirectory($directory, $recursive);
    }

    /**
     * @param string $voter
     * @param string $factory
     */
    public function addVoter(string $voter, string $factory = AutowireFactory::class): void
    {
        $this->voterSubManagerConfigurator->addFactory($voter, $factory);
    }

    /**
     * @param string $directory
     * @param bool $recursive
     */
    public function addVoterDirectory(string $directory, bool $recursive = true): void
    {
        $this->voterSubManagerConfigurator->addDirectory($directory, $recursive);
    }

    /**
     * @param string $name
     * @param int $priority
     * @return Group
     */
    public function addNavigationGroup(string $name, int $priority = 0): Group
    {
        $item = new Group($name, $priority);
        $this->navigation[$item->getName()] = $item;

        return $item;
    }

    /**
     * @param Group $item
     */
    public function remove(Group $item): void
    {
        if (!\array_key_exists($item->getName(), $this->navigation)) {
            return;
        }

        unset($this->navigation[$item->getName()]);
    }

    /**
     * @param string $name
     * @return Group
     */
    public function getNavigationGroup(string $name): Group
    {
        return $this->navigation[$name];
    }

    /**
     * @param string $message
     */
    public function setLoginMessage(string $message): void
    {
        $this->config['loginMessage'] = $message;
    }

    /**
     * @param int $sessionTimeout
     */
    public function setSessionTimeout(int $sessionTimeout): void
    {
        $this->config['sessionTimeout'] = $sessionTimeout;
    }

    /**
     * @param string $uri
     */
    public function setUri(string $uri): void
    {
        $this->config['uri'] = $uri;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $config = $this->config;
        $config['navigation'] = [];

        if (!empty($this->navigation)) {
            $queue = new SplPriorityQueue();
            foreach ($this->navigation as $group) {
                $queue->insert($group, $group->getPriority());
            }

            $queue->top();
            foreach ($queue as $group) {
                $config['navigation'][] = $group->toArray();
            }
        }

        return $config;
    }

    /**
     * @param ServiceRegistryInterface $serviceRegistry
     */
    public function registerService(ServiceRegistryInterface $serviceRegistry): void
    {
        $serviceRegistry->add(AdminProjectConfig::class, new AdminProjectConfig($this));
        $this->clientSubManagerConfigurator->registerService($serviceRegistry);
        $this->roleSubManagerConfigurator->registerService($serviceRegistry);
        $this->dashboardWidgetSubManagerConfigurator->registerService($serviceRegistry);
        $this->additionalSchemaSubManagerConfigurator->registerService($serviceRegistry);
        $this->voterSubManagerConfigurator->registerService($serviceRegistry);
    }
}
