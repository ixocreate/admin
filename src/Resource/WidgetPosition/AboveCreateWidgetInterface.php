<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Admin\Resource\WidgetPosition;

use Ixocreate\Admin\UserInterface;
use Ixocreate\Admin\Widget\WidgetCollectorInterface;

interface AboveCreateWidgetInterface
{
    public function receiveAboveCreateWidgets(UserInterface $user, WidgetCollectorInterface $widgetCollector): void;
}
