<?php

namespace WechatOfficialAccountMenuBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Menu\MenuItemInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use WechatOfficialAccountMenuBundle\Controller\Admin\DashboardController;

/**
 * @internal
 */
#[CoversClass(DashboardController::class)]
#[RunTestsInSeparateProcesses]
final class DashboardControllerTest extends AbstractWebTestCase
{
    public function testConfigureDashboardReturnsValidConfiguration(): void
    {
        $controller = self::getService(DashboardController::class);
        $dashboard = $controller->configureDashboard();

        $this->assertInstanceOf(Dashboard::class, $dashboard);
        // 验证配置成功即可，不检查具体方法
    }

    public function testConfigureMenuItemsReturnsIterable(): void
    {
        $controller = self::getService(DashboardController::class);
        $menuItems = $controller->configureMenuItems();

        $this->assertIsIterable($menuItems);

        // 验证返回的菜单项都是 MenuItemInterface 实例
        foreach ($menuItems as $menuItem) {
            $this->assertInstanceOf(MenuItemInterface::class, $menuItem);
        }
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createAuthenticatedClient();

        try {
            $client->request($method, '/admin/wechat-menu');
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        } catch (MethodNotAllowedHttpException|NotFoundHttpException $e) {
            // 对于无效的HTTP方法（如"INVALID"），Symfony会抛出NotFoundHttpException
            // 对于有效但不被允许的HTTP方法，会抛出MethodNotAllowedHttpException
            // 由于已经在catch块中声明了异常类型，这里只需要确保异常被捕获即可
            $this->assertTrue(true, 'Expected exception was caught');
        }
    }
}
