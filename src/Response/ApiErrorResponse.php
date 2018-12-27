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

namespace Ixocreate\Admin\Response;

use Zend\Diactoros\Response\JsonResponse;

final class ApiErrorResponse extends JsonResponse
{
    public function __construct(string $errorCode, array $messages = [], int $status = 200, array $notifications = [])
    {
        $payload = [
            'success' => false,
            'notifications' => $notifications,
            'errorCode' => $errorCode,
            'errorMessages' => $messages,
        ];
        parent::__construct($payload, $status);
    }
}
