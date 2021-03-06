<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Admin\Config;

use Ixocreate\Admin\AdminConfigurator;
use Ixocreate\Admin\Schema\User\LocaleAttributesSchema;
use Ixocreate\Application\Service\SerializableServiceInterface;
use Symfony\Component\Finder\SplFileInfo;

final class AdminProjectConfig implements SerializableServiceInterface
{
    private $contentTypeDefinition = [
        'css' => 'text/css',
        'eot' => 'application/vnd.ms-fontobject',
        'gif' => 'image/gif',
        'htm' => 'text/html',
        'html' => 'text/html',
        'ico' => 'image/x-icon',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'js' => 'text/javascript',
        'json' => 'application/json',
        'png' => 'image/png',
        'ppt' => 'application/vnd.ms-powerpoint',
        'svg' => 'image/svg+xml',
        'ttf' => 'application/x-font-ttf',
        'txt' => 'text/plain',
        'woff' => 'application/x-font-woff',
        'woff2' => 'font/woff2',
    ];

    private $config;

    /**
     * AdminConfig constructor.
     * @param AdminConfigurator $adminConfigurator
     */
    public function __construct(AdminConfigurator $adminConfigurator)
    {
        $this->config = $adminConfigurator->toArray();
        $this->config['adminBuildPath'] = \rtrim($this->config['adminBuildPath'], '/') . '/';

        // TODO: check if secret is set

        $items = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->adminBuildPath()), \RecursiveIteratorIterator::LEAVES_ONLY);
        /** @var SplFileInfo $file */
        foreach ($items as $name => $file) {
            if ($file->isDir()) {
                continue;
            }

            if (!$this->isValidAdminFile($file->getExtension())) {
                continue;
            }

            $name = \str_replace($this->adminBuildPath(), '', $name);

            $this->config['adminBuildFiles'][$name] = [
                'contentType' => $this->getContentType($file->getExtension()),
                'filesize' => $file->getSize(),
            ];
        }
    }

    /**
     * @return string
     */
    public function secret(): string
    {
        return $this->config['secret'];
    }

    /**
     * @return string
     */
    public function author(): string
    {
        return $this->config['author'];
    }

    /**
     * @return string
     */
    public function copyright(): string
    {
        return $this->config['copyright'];
    }

    /**
     * @return string
     */
    public function description(): string
    {
        return $this->config['description'];
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->config['name'];
    }

    /**
     * @return bool
     */
    public function poweredBy(): bool
    {
        return $this->config['poweredBy'];
    }

    /**
     * @return array
     */
    public function logo(): array
    {
        return $this->config['logo'];
    }

    /**
     * @return string
     */
    public function loginLogo(): string
    {
        return $this->config['loginLogo'];
    }

    /**
     * @return string
     */
    public function icon(): string
    {
        return $this->config['icon'];
    }

    /**
     * @return string
     */
    public function background(): string
    {
        return $this->config['background'];
    }

    /**
     * @return array
     */
    public function navigation(): array
    {
        return $this->config['navigation'];
    }

    /**
     * @return string
     */
    public function adminBuildPath(): string
    {
        return $this->config['adminBuildPath'];
    }

    /**
     * @return array
     */
    public function adminBuildFiles(): array
    {
        return $this->config['adminBuildFiles'];
    }

    /**
     * @return array
     */
    public function clientConfigProvider(): array
    {
        return $this->config['clientConfigProvider'];
    }

    /**
     * @return string
     */
    public function userAttributesSchema(): ?string
    {
        return $this->config['userAttributesSchema'];
    }

    /**
     * @return string
     */
    public function accountAttributesSchema(): ?string
    {
        return $this->config['accountAttributesSchema'];
    }

    /**
     * @return string
     */
    public function localeAttributesSchema(): ?string
    {
        return $this->config['localeAttributesSchema'];
    }

    /**
     * @return string
     */
    public function defaultLocale(): string
    {
        return $this->config['defaultLocale'];
    }

    /**
     * @return string
     */
    public function defaultTimezone(): string
    {
        return $this->config['defaultTimezone'];
    }

    /**
     * @return string
     */
    public function loginMessage(): string
    {
        return $this->config['loginMessage'];
    }

    /**
     * @return string|null
     */
    public function googleMapApiKey(): ?string
    {
        return $this->config['googleMapApiKey'];
    }

    /**
     * @return int
     */
    public function sessionTimeout(): int
    {
        return $this->config['sessionTimeout'];
    }

    /**
     * @return string
     */
    public function uri(): string
    {
        return $this->config['uri'];
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return \serialize($this->config);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $this->config = \unserialize($serialized);
    }

    private function isValidAdminFile($extension): bool
    {
        return !empty($this->contentTypeDefinition[$extension]);
    }

    private function getContentType($extension): string
    {
        return $this->contentTypeDefinition[$extension];
    }
}
