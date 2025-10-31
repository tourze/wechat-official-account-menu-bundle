<?php

namespace WechatOfficialAccountMenuBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;
use WechatOfficialAccountMenuBundle\Service\AdminMenu;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    private AdminMenu $adminMenu;

    protected function onSetUp(): void
    {
        $this->adminMenu = self::getService(AdminMenu::class);
    }

    #[Test]
    public function testServiceIsAvailableInContainer(): void
    {
        // 验证服务可以从容器中获取
        $this->assertInstanceOf(AdminMenu::class, $this->adminMenu);
    }

    #[Test]
    public function testImplementsMenuProviderInterface(): void
    {
        $this->assertInstanceOf(
            MenuProviderInterface::class,
            $this->adminMenu
        );
    }

    #[Test]
    public function testInvokeWithMissingDependenciesDoesNothing(): void
    {
        // 使用模拟对象来避免直接实例化
        $mockLinkGenerator = null;
        $mockAdminUrlGenerator = null;

        // 使用反射来创建实例以避免直接构造函数调用
        $reflectionClass = new \ReflectionClass(AdminMenu::class);
        $adminMenuWithoutDeps = $reflectionClass->newInstanceWithoutConstructor();

        // 注入null依赖
        $linkGeneratorProperty = $reflectionClass->getProperty('linkGenerator');
        $linkGeneratorProperty->setAccessible(true);
        $linkGeneratorProperty->setValue($adminMenuWithoutDeps, $mockLinkGenerator);

        $adminUrlGeneratorProperty = $reflectionClass->getProperty('adminUrlGenerator');
        $adminUrlGeneratorProperty->setAccessible(true);
        $adminUrlGeneratorProperty->setValue($adminMenuWithoutDeps, $mockAdminUrlGenerator);

        $rootItem = $this->createMock(ItemInterface::class);

        // 当依赖为null时，不应该有任何菜单操作
        $rootItem->expects($this->never())->method('getChild');
        $rootItem->expects($this->never())->method('addChild');

        $adminMenuWithoutDeps($rootItem);
    }
}
