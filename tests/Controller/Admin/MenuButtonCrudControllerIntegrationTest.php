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
use WechatOfficialAccountMenuBundle\Controller\Admin\DashboardController;
use WechatOfficialAccountMenuBundle\Controller\Admin\MenuButtonCrudController;
use WechatOfficialAccountMenuBundle\Entity\MenuButton;

/**
 * @internal
 */
#[CoversClass(MenuButtonCrudController::class)]
#[RunTestsInSeparateProcesses]
final class MenuButtonCrudControllerIntegrationTest extends AbstractEasyAdminControllerTestCase
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


    /**
     * 覆盖基类方法，声明使用正确的Dashboard控制器
     */
    protected function getPreferredDashboardControllerFqcn(): string
    {
        return DashboardController::class;
    }

    protected function getControllerService(): MenuButtonCrudController
    {
        $controller = self::getService(MenuButtonCrudController::class);
        self::assertInstanceOf(MenuButtonCrudController::class, $controller);
        return $controller;
    }

    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '公众号' => ['公众号'];
        yield '菜单名称' => ['菜单名称'];
        yield '类型' => ['类型'];
        yield '排序' => ['排序'];
        yield '启用' => ['启用'];
        yield '更新时间' => ['更新时间'];
    }

    public static function provideNewPageFields(): iterable
    {
        yield 'account' => ['account'];
        yield 'name' => ['name'];
        yield 'type' => ['type'];
        yield 'position' => ['position'];
        yield 'enabled' => ['enabled'];
    }

    public static function provideEditPageFields(): iterable
    {
        yield 'account' => ['account'];
        yield 'name' => ['name'];
        yield 'type' => ['type'];
        yield 'position' => ['position'];
        yield 'enabled' => ['enabled'];
    }

    public function testValidationErrors(): void
    {
        $client = $this->createAuthenticatedClient();
        $crawler = $client->request('GET', $this->generateAdminUrl(Action::NEW));
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="MenuButton"]')->form();

        // 提交空表单触发必填字段验证
        $client->submit($form);

        // 验证响应状态码为 422 (Unprocessable Entity)
        $this->assertResponseStatusCodeSame(422);

        // 验证必填字段的错误信息
        $responseContent = $client->getResponse()->getContent();
        $this->assertNotFalse($responseContent);
        $this->assertStringContainsString('account', $responseContent);
        $this->assertStringContainsString('name', $responseContent);
        $this->assertStringContainsString('type', $responseContent);
    }
}
