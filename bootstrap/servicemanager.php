<?php
declare(strict_types=1);

namespace Ixocreate\Admin;

/** @var \Ixocreate\ServiceManager\ServiceManagerConfigurator $serviceManager */
use Ixocreate\Admin\Config\AdminConfig;
use Ixocreate\Admin\Config\Client\ClientConfigGenerator;
use Ixocreate\Admin\Config\Client\ClientConfigProviderSubManager;
use Ixocreate\Admin\Config\Factory\AdminConfigFactory;
use Ixocreate\Admin\Dashboard\DashboardWidgetProviderSubManager;
use Ixocreate\Admin\Helper\Factory\ServerUrlHelperFactory;
use Ixocreate\Admin\Helper\Factory\UrlHelperFactory;
use Ixocreate\Admin\Helper\ServerUrlHelper;
use Ixocreate\Admin\Helper\UrlHelper;
use Ixocreate\Admin\Role\RoleSubManager;
use Ixocreate\Admin\Router\AdminRouter;
use Ixocreate\Admin\Router\Factory\AdminRouterFactory;

$serviceManager->addFactory(AdminConfig::class, AdminConfigFactory::class);
$serviceManager->addFactory(AdminRouter::class, AdminRouterFactory::class);
$serviceManager->addFactory(ServerUrlHelper::class, ServerUrlHelperFactory::class);
$serviceManager->addFactory(UrlHelper::class, UrlHelperFactory::class);
$serviceManager->addFactory(ClientConfigGenerator::class);

$serviceManager->addSubManager(RoleSubManager::class);
$serviceManager->addSubManager(ClientConfigProviderSubManager::class);
$serviceManager->addSubManager(DashboardWidgetProviderSubManager::class);
