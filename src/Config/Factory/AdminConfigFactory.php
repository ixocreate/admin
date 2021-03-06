<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Admin\Config\Factory;

use Ixocreate\Admin\Config\AdminConfig;
use Ixocreate\Admin\Config\AdminProjectConfig;
use Ixocreate\Application\Uri\ApplicationUri;
use Ixocreate\Asset\Asset;
use Ixocreate\ServiceManager\FactoryInterface;
use Ixocreate\ServiceManager\ServiceManagerInterface;
use Laminas\Diactoros\Uri;

final class AdminConfigFactory implements FactoryInterface
{
    /**
     * @param ServiceManagerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @return mixed
     */
    public function __invoke(ServiceManagerInterface $container, $requestedName, array $options = null)
    {
        /** @var AdminProjectConfig $config */
        $config = $container->get(AdminProjectConfig::class);

        $uri = new Uri($config->uri());
        if (empty($uri->getHost())) {
            /** @var ApplicationUri $projectUri */
            $projectUri = $container->get(ApplicationUri::class);

            $uri = $uri->withPath($projectUri->getMainUri()->getPath() . $uri->getPath());
            $uri = $uri->withHost($projectUri->getMainUri()->getHost());
            $uri = $uri->withScheme($projectUri->getMainUri()->getScheme());
            $uri = $uri->withPort($projectUri->getMainUri()->getPort());
        }

        return new AdminConfig($config, $uri, $container->get(Asset::class));
    }
}
