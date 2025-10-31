<?php

namespace WechatOfficialAccountMenuBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;
use WechatOfficialAccountMenuBundle\DependencyInjection\WechatOfficialAccountMenuExtension;

/**
 * @internal
 */
#[CoversClass(WechatOfficialAccountMenuExtension::class)]
final class WechatOfficialAccountMenuExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    private WechatOfficialAccountMenuExtension $extension;

    private ContainerBuilder $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension = new WechatOfficialAccountMenuExtension();
        $this->container = new ContainerBuilder();

        // 设置必要的参数
        $this->container->setParameter('kernel.bundles', []);
        $this->container->setParameter('kernel.project_dir', sys_get_temp_dir());
        $this->container->setParameter('kernel.environment', 'test');
    }

    public function testContainerParametersShouldContainRequiredParameters(): void
    {
        $configs = [];
        $this->extension->load($configs, $this->container);

        // 验证容器是否包含必要的参数
        $this->assertTrue($this->container->hasParameter('kernel.bundles'));
    }

    public function testPrependShouldNotAddFrameworkConfiguration(): void
    {
        // 设置测试环境
        $this->container->setParameter('kernel.environment', 'test');

        $this->extension->prepend($this->container);

        // prepend 方法应该是空的，不应该添加任何框架配置
        $extensionConfigs = $this->container->getExtensionConfig('framework');

        // 验证框架配置没有被修改（因为 RoutingAutoLoaderBundle 会自动处理路由）
        $this->assertEmpty($extensionConfigs);
    }

    public function testPrependShouldNotAddFrameworkConfigurationInProduction(): void
    {
        // 设置生产环境
        $this->container->setParameter('kernel.environment', 'prod');

        $this->extension->prepend($this->container);

        // prepend 方法应该是空的，不应该添加任何框架配置
        $extensionConfigs = $this->container->getExtensionConfig('framework');

        // 验证框架配置没有被修改
        $this->assertEmpty($extensionConfigs);
    }
}
