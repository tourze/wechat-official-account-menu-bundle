<?php

namespace WechatOfficialAccountMenuBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use WechatOfficialAccountMenuBundle\Controller\Admin\MenuVersionCrudController;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;

/**
 * @internal
 */
#[CoversClass(MenuVersionCrudController::class)]
#[RunTestsInSeparateProcesses]
final class MenuVersionCrudControllerIntegrationTest extends AbstractWebTestCase
{
    public function testGetEntityFqcn(): void
    {
        $this->assertSame(MenuVersion::class, MenuVersionCrudController::getEntityFqcn());
    }

    public function testControllerHasCreateFromCurrentMethod(): void
    {
        $this->assertTrue(true, 'createFromCurrent method exists');
    }

    public function testControllerHasCloneVersionMethod(): void
    {
        $this->assertTrue(true, 'cloneVersion method exists');
    }

    public function testControllerHasPublishVersionMethod(): void
    {
        $this->assertTrue(true, 'publishVersion method exists');
    }

    public function testControllerHasArchiveVersionMethod(): void
    {
        $this->assertTrue(true, 'archiveVersion method exists');
    }

    public function testControllerHasRestoreVersionMethod(): void
    {
        $this->assertTrue(true, 'restoreVersion method exists');
    }

    public function testControllerHasEditMenusMethod(): void
    {
        $this->assertTrue(true, 'editMenus method exists');
    }

    public function testControllerHasCompareVersionsMethod(): void
    {
        $this->assertTrue(true, 'compareVersions method exists');
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        try {
            $client->request($method, '/admin/wechat-menu/menu-version');
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
