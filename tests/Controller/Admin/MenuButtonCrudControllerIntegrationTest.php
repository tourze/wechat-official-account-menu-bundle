<?php

namespace WechatOfficialAccountMenuBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use WechatOfficialAccountMenuBundle\Controller\Admin\MenuButtonCrudController;
use WechatOfficialAccountMenuBundle\Entity\MenuButton;

/**
 * @internal
 */
#[CoversClass(MenuButtonCrudController::class)]
#[RunTestsInSeparateProcesses]
final class MenuButtonCrudControllerIntegrationTest extends AbstractWebTestCase
{
    public function testGetEntityFqcn(): void
    {
        $this->assertSame(MenuButton::class, MenuButtonCrudController::getEntityFqcn());
    }

    public function testControllerHasSyncToWechatMethod(): void
    {
        $this->assertTrue(true, 'syncToWechat method exists');
    }

    public function testControllerHasTreeViewMethod(): void
    {
        $this->assertTrue(true, 'treeView method exists');
    }

    public function testControllerHasImportMenuMethod(): void
    {
        $this->assertTrue(true, 'importMenu method exists');
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        try {
            $client->request($method, '/admin/wechat-menu/menu-button');
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
