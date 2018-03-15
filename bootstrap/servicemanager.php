<?php
declare(strict_types=1);

namespace KiwiSuite\Admin;

/** @var \KiwiSuite\ServiceManager\ServiceManagerConfigurator $serviceManager */
use KiwiSuite\Admin\Config\AdminConfig;
use KiwiSuite\Admin\Config\Factory\AdminConfigFactory;
use KiwiSuite\Admin\Helper\Factory\ServerUrlHelperFactory;
use KiwiSuite\Admin\Helper\Factory\UrlHelperFactory;
use KiwiSuite\Admin\Helper\ServerUrlHelper;
use KiwiSuite\Admin\Helper\UrlHelper;
use KiwiSuite\Admin\Resource\ResourceSubManager;
use KiwiSuite\Admin\Role\RoleSubManager;
use KiwiSuite\Admin\Router\AdminRouter;
use KiwiSuite\Admin\Router\Factory\AdminRouterFactory;
use KiwiSuite\Template\Plates\PlatesRendererFactory;
use Zend\Expressive\Plates\PlatesRenderer;

$serviceManager->addFactory(AdminConfig::class, AdminConfigFactory::class);
$serviceManager->addFactory(AdminRouter::class, AdminRouterFactory::class);
$serviceManager->addFactory(ServerUrlHelper::class, ServerUrlHelperFactory::class);
$serviceManager->addFactory(UrlHelper::class, UrlHelperFactory::class);
$serviceManager->addFactory(PlatesRenderer::class, PlatesRendererFactory::class);

$serviceManager->addSubManager(RoleSubManager::class);
$serviceManager->addSubManager(ResourceSubManager::class);
