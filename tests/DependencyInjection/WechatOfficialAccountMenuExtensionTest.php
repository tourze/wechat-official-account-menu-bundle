<?php

namespace WechatOfficialAccountMenuBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use WechatOfficialAccountMenuBundle\DependencyInjection\WechatOfficialAccountMenuExtension;
use WechatOfficialAccountMenuBundle\Repository\MenuButtonRepository;

class WechatOfficialAccountMenuExtensionTest extends TestCase
{
    private WechatOfficialAccountMenuExtension $extension;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->extension = new WechatOfficialAccountMenuExtension();
        $this->container = new ContainerBuilder();
        
        // 设置必要的参数
        $this->container->setParameter('kernel.bundles', []);
        $this->container->setParameter('kernel.project_dir', sys_get_temp_dir());
    }

    public function testLoad_shouldRegisterServices(): void
    {
        $configs = [];
        $this->extension->load($configs, $this->container);
        
        // 验证services.yaml中定义的服务是否已正确注册
        $this->assertTrue($this->container->has(MenuButtonRepository::class));
        
        $definition = $this->container->getDefinition(MenuButtonRepository::class);
        $this->assertTrue($definition->isAutowired());
        $this->assertTrue($definition->isAutoconfigured());
    }

    public function testContainerParameters_shouldContainRequiredParameters(): void
    {
        $configs = [];
        $this->extension->load($configs, $this->container);
        
        // 验证容器是否包含必要的参数
        $this->assertTrue($this->container->hasParameter('kernel.bundles'));
    }
} 