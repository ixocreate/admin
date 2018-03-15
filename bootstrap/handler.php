<?php
declare(strict_types=1);

namespace KiwiSuite\Admin;

/** @var HandlerConfigurator $handler */
use KiwiSuite\CommandBus\Handler\HandlerConfigurator;

$handler->addDirectory( __DIR__ . '/../src/Handler/', true);

