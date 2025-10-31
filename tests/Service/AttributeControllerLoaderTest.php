<?php

namespace WechatOfficialAccountMenuBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Routing\RouteCollection;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use WechatOfficialAccountMenuBundle\Service\AttributeControllerLoader;

/**
 * @internal
 */
#[CoversClass(AttributeControllerLoader::class)]
#[RunTestsInSeparateProcesses]
final class AttributeControllerLoaderTest extends AbstractIntegrationTestCase
{
    private AttributeControllerLoader $loader;

    protected function onSetUp(): void
    {
        $this->loader = self::getService(AttributeControllerLoader::class);
    }

    public function testLoadReturnsRouteCollection(): void
    {
        $routes = $this->loader->load('SomeController', '');

        // 验证load方法返回RouteCollection实例
        $this->assertInstanceOf(RouteCollection::class, $routes);
        // 验证返回的路由集合不为空
        $this->assertGreaterThan(0, $routes->count());
    }

    public function testSupportsReturnsFalseForNonAttributeType(): void
    {
        self::assertFalse($this->loader->supports('', 'annotation'));
        self::assertFalse($this->loader->supports('', 'yaml'));
        self::assertFalse($this->loader->supports('', 'xml'));
    }

    public function testSupportsReturnsTrueForAttributeType(): void
    {
        self::assertTrue($this->loader->supports('', 'attribute'));
    }

    public function testAutoload(): void
    {
        $result = $this->loader->autoload();

        $this->assertInstanceOf(RouteCollection::class, $result);
        // 验证返回的集合不为空（包含了所有控制器的路由）
        $this->assertGreaterThan(0, $result->count());
    }
}
