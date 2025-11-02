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

        // 为 Twig 注册命名空间，供模板使用 @WechatOfficialAccountMenu 前缀
        // 这样诸如 setTemplatePath('@WechatOfficialAccountMenu/...') 的调用才能解析到本 Bundle 的 templates 目录
        $bundleRoot = realpath(__DIR__ . '/../..');
        if ($bundleRoot !== false) {
            $templatesPath = $bundleRoot . '/templates';
            if (is_dir($templatesPath)) {
                $container->prependExtensionConfig('twig', [
                    'paths' => [
                        $templatesPath => 'WechatOfficialAccountMenu',
                    ],
                ]);
            }
        }
    }
}
