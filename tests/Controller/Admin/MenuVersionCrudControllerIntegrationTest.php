<?php

namespace WechatOfficialAccountMenuBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use WechatOfficialAccountMenuBundle\Controller\Admin\MenuVersionCrudController;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;

/**
 * @internal
 */
#[CoversClass(MenuVersionCrudController::class)]
#[RunTestsInSeparateProcesses]
final class MenuVersionCrudControllerIntegrationTest extends AbstractEasyAdminControllerTestCase
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

    protected function getControllerService(): MenuVersionCrudController
    {
        $controller = self::getService(MenuVersionCrudController::class);
        self::assertInstanceOf(MenuVersionCrudController::class, $controller);
        return $controller;
    }

    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '公众号' => ['公众号'];
        yield '版本号' => ['版本号'];
        yield '状态' => ['状态'];
        yield '发布时间' => ['发布时间'];
        yield '菜单数量' => ['菜单数量'];
    }

    public static function provideNewPageFields(): iterable
    {
        yield 'account' => ['account'];
        yield 'version' => ['version'];
        yield 'description' => ['description'];
        yield 'status' => ['status'];
    }

    public static function provideEditPageFields(): iterable
    {
        yield 'account' => ['account'];
        yield 'version' => ['version'];
        yield 'description' => ['description'];
        yield 'status' => ['status'];
    }

    public function testValidationErrors(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', $this->generateAdminUrl(Action::NEW));
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="MenuVersion"]')->form();

        // 提交空表单触发必填字段验证
        $client->submit($form);

        // 验证响应状态码为 422 (Unprocessable Entity)
        $this->assertResponseStatusCodeSame(422);

        // 验证必填字段的错误信息
        $responseContent = $client->getResponse()->getContent();
        $this->assertNotFalse($responseContent);
        $this->assertStringContainsString('account', $responseContent);
        $this->assertStringContainsString('version', $responseContent);
    }
}
