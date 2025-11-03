<?php

namespace WechatOfficialAccountMenuBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\RoutingAutoLoaderBundle\RoutingAutoLoaderBundle;
use WechatOfficialAccountBundle\WechatOfficialAccountBundle;
use Tourze\EasyAdminMenuBundle\EasyAdminMenuBundle;

class WechatOfficialAccountMenuBundle extends Bundle implements BundleDependencyInterface
{
    /**
     * @return array<class-string<Bundle>, array{all?: bool}>
     */
    public static function getBundleDependencies(): array
    {
        return [
            DoctrineBundle::class => ['all' => true],
            EasyAdminBundle::class => ['all' => true],
            SecurityBundle::class => ['all' => true],
            TwigBundle::class => ['all' => true],
            WechatOfficialAccountBundle::class => ['all' => true],
            RoutingAutoLoaderBundle::class => ['all' => true],
            EasyAdminMenuBundle::class => ['all' => true],
        ];
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        // 注册Twig模板路径
        $templatePath = __DIR__ . '/../templates';
        if (is_dir($templatePath)) {
            $container->prependExtensionConfig('twig', [
                'paths' => [
                    $templatePath => 'WechatOfficialAccountMenu',
                ],
            ]);
        }

        // 在测试环境下加载Controller服务配置
        if ('test' === $container->getParameter('kernel.environment')) {
            // 手动注册测试环境需要的Controller服务
            $container->register('WechatOfficialAccountMenuBundle\Controller\Test\LoginController')
                ->setClass('WechatOfficialAccountMenuBundle\Controller\Test\LoginController')
                ->setAutowired(true)
                ->setAutoconfigured(true)
                ->setPublic(true)
                ->addTag('controller.service_arguments')
            ;

            $container->register('WechatOfficialAccountMenuBundle\Controller\Test\LogoutController')
                ->setClass('WechatOfficialAccountMenuBundle\Controller\Test\LogoutController')
                ->setAutowired(true)
                ->setAutoconfigured(true)
                ->setPublic(true)
                ->addTag('controller.service_arguments')
            ;

            $container->register('WechatOfficialAccountMenuBundle\Controller\Admin\DashboardController')
                ->setClass('WechatOfficialAccountMenuBundle\Controller\Admin\DashboardController')
                ->setAutowired(true)
                ->setAutoconfigured(true)
                ->setPublic(true)
                ->addTag('controller.service_arguments')
            ;

            $container->register('WechatOfficialAccountMenuBundle\Controller\Admin\MenuButtonCrudController')
                ->setClass('WechatOfficialAccountMenuBundle\Controller\Admin\MenuButtonCrudController')
                ->setAutowired(true)
                ->setAutoconfigured(true)
                ->setPublic(true)
                ->addTag('controller.service_arguments')
            ;

            $container->register('WechatOfficialAccountMenuBundle\Controller\Admin\MenuVersionCrudController')
                ->setClass('WechatOfficialAccountMenuBundle\Controller\Admin\MenuVersionCrudController')
                ->setAutowired(true)
                ->setAutoconfigured(true)
                ->setPublic(true)
                ->addTag('controller.service_arguments')
            ;

            // 配置测试用的 Security provider
            $container->prependExtensionConfig('security', [
                'providers' => [
                    'test_memory_provider' => [
                        'id' => 'WechatOfficialAccountMenuBundle\Tests\Test\TestMemoryUserProvider',
                    ],
                ],
            ]);
        }
    }
}
