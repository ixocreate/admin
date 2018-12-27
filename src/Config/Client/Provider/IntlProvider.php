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

namespace Ixocreate\Admin\Config\Client\Provider;

use Ixocreate\Contract\Admin\ClientConfigProviderInterface;
use Ixocreate\Contract\Admin\RoleInterface;
use Ixocreate\Intl\LocaleManager;

final class IntlProvider implements ClientConfigProviderInterface
{
    /**
     * @var LocaleManager
     */
    private $localeManager;

    public function __construct(LocaleManager $localeManager)
    {
        $this->localeManager = $localeManager;
    }

    /**
     * @param RoleInterface|null $role
     * @return array
     */
    public function clientConfig(?RoleInterface $role = null): array
    {
        if (empty($role)) {
            return [];
        }

        return [
            'default' => $this->localeManager->defaultLocale(),
            'locales' => $this->localeManager->all(),
        ];
    }

    public static function serviceName(): string
    {
        return 'intl';
    }
}
