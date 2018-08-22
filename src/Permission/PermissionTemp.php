<?php
/**
 * kiwi-suite/admin (https://github.com/kiwi-suite/admin)
 *
 * @package kiwi-suite/admin
 * @link https://github.com/kiwi-suite/admin
 * @copyright Copyright (c) 2010 - 2018 kiwi suite GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace KiwiSuite\Admin\Permission;

use KiwiSuite\Contract\Admin\RoleInterface;

final class PermissionTemp
{
    /**
     * @var RoleInterface
     */
    private $role;

    public function __construct(RoleInterface $role)
    {
        $this->role = $role;
    }

    public function withUser(RoleInterface $role): Permission
    {
        return new Permission($role);
    }

    public function can(string $permission): bool
    {
        if (\in_array($permission, $this->role->getPermissions())) {
            return true;
        }

        if (\in_array("*", $this->role->getPermissions())) {
            return true;
        }

        $permissionParts = \explode('.', $permission);

        for ($i = 0; $i < \count($permissionParts); $i++) {
            $checkPermission = [];
            for ($j = 0; $j <= $i; $j++) {
                $checkPermission[] = $permissionParts[$j];
                if (\in_array(\implode('.', $checkPermission), $role->getPermissions())) {
                    return true;
                }
                if (\in_array(\implode('.', $checkPermission) . '.*', $role->getPermissions())) {
                    return true;
                }
            }
        }

        return false;
    }
}