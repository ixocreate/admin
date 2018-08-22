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

namespace KiwiSuite\Admin\Action\Api\Config;

use KiwiSuite\Admin\Config\Client\ClientConfigGenerator;
use KiwiSuite\Admin\Entity\User;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ConfigAction implements MiddlewareInterface
{
    /**
     * @var ClientConfigGenerator
     */
    private $clientConfigGenerator;

    public function __construct(ClientConfigGenerator $clientConfigGenerator)
    {
        $this->clientConfigGenerator = $clientConfigGenerator;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $role = null;
        if ($user = $request->getAttribute(User::class)) {
            $role = $user->role()->getRole();
        }

        return new ApiSuccessResponse($this->clientConfigGenerator->generate($role));
    }
}
