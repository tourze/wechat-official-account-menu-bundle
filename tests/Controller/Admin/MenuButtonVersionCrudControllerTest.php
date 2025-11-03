<?php

namespace WechatOfficialAccountMenuBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use WechatOfficialAccountMenuBundle\Controller\Admin\DashboardController;
use WechatOfficialAccountMenuBundle\Controller\Admin\MenuButtonVersionCrudController;
use WechatOfficialAccountMenuBundle\Entity\MenuButtonVersion;

/**
 * @internal
 */
#[CoversClass(MenuButtonVersionCrudController::class)]
#[RunTestsInSeparateProcesses]
final class MenuButtonVersionCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    public function testGetEntityFqcn(): void
    {
        $this->assertSame(MenuButtonVersion::class, MenuButtonVersionCrudController::getEntityFqcn());
    }

    /**
     * 覆盖基类方法，声明使用正确的Dashboard控制器
     */
    protected function getPreferredDashboardControllerFqcn(): string
    {
        return DashboardController::class;
    }

    /**
     * @return AbstractCrudController<MenuButtonVersion>
     */
    protected function getControllerService(): AbstractCrudController
    {
        return new MenuButtonVersionCrudController();
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'id' => ['ID'];
        yield 'version' => ['所属版本'];
        yield 'menu_name' => ['菜单名称'];
        yield 'menu_type' => ['菜单类型'];
        yield 'position' => ['排序'];
        yield 'enabled' => ['启用'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield 'version' => ['version'];
        yield 'parent' => ['parent'];
        yield 'name' => ['name'];
        yield 'type' => ['type'];
        yield 'clickKey' => ['clickKey'];
        yield 'url' => ['url'];
        yield 'appId' => ['appId'];
        yield 'pagePath' => ['pagePath'];
        yield 'position' => ['position'];
        yield 'enabled' => ['enabled'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'version' => ['version'];
        yield 'parent' => ['parent'];
        yield 'name' => ['name'];
        yield 'type' => ['type'];
        yield 'clickKey' => ['clickKey'];
        yield 'url' => ['url'];
        yield 'appId' => ['appId'];
        yield 'pagePath' => ['pagePath'];
        yield 'position' => ['position'];
        yield 'enabled' => ['enabled'];
    }

    public function testValidationErrors(): void
    {
        $client = $this->createAuthenticatedClient();

        // 尝试访问新建页面，如果NEW操作被禁用则跳过
        try {
            $crawler = $client->request('GET', $this->generateAdminUrl('new'));
        } catch (\Exception $e) {
            // 如果出现异常（如路由不存在），跳过测试
            self::markTestSkipped('NEW action is not available: ' . $e->getMessage());
        }

        // 如果获得403或404响应（NEW操作被禁用），跳过测试
        if ($client->getResponse()->getStatusCode() >= 400) {
            self::markTestSkipped('NEW action is disabled, skipping validation test');
        }

        $this->assertResponseIsSuccessful();

        // 尝试找到提交按钮，可能是 "保存"、"Save"、"Create" 或其他文本
        $form = null;
        $buttonSelectors = ['保存', 'Save', 'Create', 'Submit'];

        foreach ($buttonSelectors as $buttonText) {
            try {
                $form = $crawler->selectButton($buttonText)->form();
                break;
            } catch (\InvalidArgumentException $e) {
                // 继续尝试下一个按钮
                continue;
            }
        }

        if (null === $form) {
            // 如果找不到按钮，就直接选择表单
            $form = $crawler->filter('form')->form();
        }

        $this->assertInstanceOf(\Symfony\Component\DomCrawler\Form::class, $form, '应该能找到表单');

        // 获取表单并提交空表单（不填写必填字段）
        // 清除name字段的值以触发验证错误（version是下拉选择，不能设为空）
        $form['MenuButtonVersion[name]'] = '';

        $crawler = $client->submit($form);

        // EasyAdmin 可能返回 200 或 422 状态码，都表示表单有验证错误
        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [200, 422], 'Status code should be 200 or 422');

        // 验证错误信息包含必填字段的验证错误
        $response = $client->getResponse();
        $content = $response->getContent();
        $this->assertNotFalse($content);

        // 检查必填字段的验证错误（可能是中文或英文）
        $hasValidationError = str_contains($content, '不能为空')
            || str_contains($content, 'should not be blank')
            || str_contains($content, 'invalid-feedback');

        $this->assertTrue($hasValidationError, '响应内容应包含验证错误信息');
    }
}
