<?php

namespace WechatOfficialAccountMenuBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class WechatOfficialAccountMenuExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }

    public function prepend(ContainerBuilder $container): void
    {
        // RoutingAutoLoaderBundle 会自动加载 routing.yaml，不需要手动配置
    }
}
