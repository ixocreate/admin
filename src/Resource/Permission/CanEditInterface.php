<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Admin\Resource\Permission;

use Ixocreate\Admin\UserInterface;

interface CanEditInterface
{
    public function canEdit(UserInterface $user): bool;
}
