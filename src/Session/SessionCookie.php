<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Admin\Session;

use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookie;
use Firebase\JWT\JWT;
use Ixocreate\Admin\Entity\SessionData;
use Ixocreate\CommonTypes\Entity\UuidType;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class SessionCookie
{
    public function createSessionCookie(ServerRequestInterface $request, ResponseInterface $response, SessionData $sessionData): ResponseInterface
    {
        $data = [
            'xsrfToken' => (string) $sessionData->xsrfToken()->getValue(),
        ];

        if ($sessionData->userId() instanceof UuidType) {
            $data['userId'] = $sessionData->userId()->getValue();
        }

        $jwt = JWT::encode(
            [
                'iat' => \time(),
                'jti' => \base64_encode(\random_bytes(32)),
                'iss' => $request->getUri()->getHost(),
                'nbf' => \time(),
                'exp' => \time() + 31536000,
                'data' => $data,
            ],
            'secret_key',
            'HS512'
        );

        $cookie = SetCookie::create("kiwiSid")
            ->withValue($jwt)
            ->withPath("/")
            ->withHttpOnly(true)
            ->withSecure(($request->getUri()->getScheme() === "https"));

        return FigResponseCookies::set($response, $cookie);
    }

    public function createXsrfCookie(ServerRequestInterface $request, ResponseInterface $response, SessionData $sessionData): ResponseInterface
    {
        $cookie = SetCookie::create("XSRF-TOKEN")
            ->withValue($sessionData->xsrfToken()->getValue())
            ->withPath("/")
            ->withHttpOnly(false)
            ->withSecure(($request->getUri()->getScheme() === "https"));

        return FigResponseCookies::set($response, $cookie);
    }
}
