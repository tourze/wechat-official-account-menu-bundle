<?php

namespace WechatOfficialAccountMenuBundle\Tests\Request\Menu;

use HttpClientBundle\Tests\Request\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use WechatOfficialAccountBundle\Request\WithAccountRequest;
use WechatOfficialAccountMenuBundle\Request\Menu\DeleteConditionalMenuRequest;

/**
 * @internal
 */
#[CoversClass(DeleteConditionalMenuRequest::class)]
final class DeleteConditionalMenuRequestTest extends RequestTestCase
{
    public function testRequestShouldExtendWithAccountRequest(): void
    {
        $request = new DeleteConditionalMenuRequest();

        $this->assertInstanceOf(WithAccountRequest::class, $request);
    }

    public function testGetRequestPathShouldReturnCorrectPath(): void
    {
        $request = new DeleteConditionalMenuRequest();

        $path = $request->getRequestPath();

        $this->assertEquals('https://api.weixin.qq.com/cgi-bin/menu/delconditional', $path);
    }

    public function testGetRequestMethodShouldReturnPost(): void
    {
        $request = new DeleteConditionalMenuRequest();

        $method = $request->getRequestMethod();

        $this->assertEquals('POST', $method);
    }

    public function testSetMenuIdShouldSetMenuId(): void
    {
        $menuId = 'menu_12345';

        $request = new DeleteConditionalMenuRequest();
        $request->setMenuId($menuId);

        $this->assertEquals($menuId, $request->getMenuId());
    }

    public function testGetMenuIdShouldReturnMenuId(): void
    {
        $menuId = 'test_menu_id_67890';

        $request = new DeleteConditionalMenuRequest();
        $request->setMenuId($menuId);

        $result = $request->getMenuId();

        $this->assertIsString($result);
        $this->assertEquals($menuId, $result);
    }

    public function testGetRequestOptionsShouldReturnJsonWithMenuId(): void
    {
        $menuId = 'conditional_menu_123';

        $request = new DeleteConditionalMenuRequest();
        $request->setMenuId($menuId);

        $options = $request->getRequestOptions();

        /** @var array{json: array{menuid: string}} $options */
        $this->assertIsArray($options);
        $this->assertArrayHasKey('json', $options);
        $this->assertIsArray($options['json']);
        $this->assertArrayHasKey('menuid', $options['json']);
        $this->assertEquals($menuId, $options['json']['menuid']);
    }

    public function testGetRequestOptionsStructureShouldBeCorrect(): void
    {
        $menuId = 'test_menu_999';

        $request = new DeleteConditionalMenuRequest();
        $request->setMenuId($menuId);

        $options = $request->getRequestOptions();

        // 验证选项结构
        /** @var array{json: array{menuid: string}} $options */
        $this->assertIsArray($options);
        $this->assertCount(1, $options);
        $this->assertArrayHasKey('json', $options);

        // 验证JSON数据结构
        $jsonData = $options['json'];
        $this->assertIsArray($jsonData);
        $this->assertCount(1, $jsonData);
        $this->assertArrayHasKey('menuid', $jsonData);
    }

    public function testMenuIdKeyInRequestOptionsShouldBeLowercase(): void
    {
        $menuId = 'menu_test_case';

        $request = new DeleteConditionalMenuRequest();
        $request->setMenuId($menuId);

        $options = $request->getRequestOptions();
        /** @var array{json: array<string, mixed>} $options */
        $jsonData = $options['json'];

        // 验证是小写的 "menuid" 而不是其他变体
        $this->assertArrayHasKey('menuid', $jsonData);
        $this->assertArrayNotHasKey('menuId', $jsonData);
        $this->assertArrayNotHasKey('menu_id', $jsonData);
        $this->assertArrayNotHasKey('menuID', $jsonData);
    }

    public function testMenuIdWithSpecialCharacters(): void
    {
        $menuId = 'menu_with-special_chars.123';

        $request = new DeleteConditionalMenuRequest();
        $request->setMenuId($menuId);

        $result = $request->getMenuId();
        $options = $request->getRequestOptions();

        /** @var array{json: array{menuid: string}} $options */
        $this->assertEquals($menuId, $result);
        $this->assertEquals($menuId, $options['json']['menuid']);
    }

    public function testMenuIdWithNumericString(): void
    {
        $menuId = '12345';

        $request = new DeleteConditionalMenuRequest();
        $request->setMenuId($menuId);

        $result = $request->getMenuId();
        $options = $request->getRequestOptions();

        /** @var array{json: array{menuid: string}} $options */
        $this->assertIsString($result);
        $this->assertEquals($menuId, $result);
        $this->assertEquals($menuId, $options['json']['menuid']);
    }

    public function testEmptyMenuIdShouldWork(): void
    {
        $menuId = '';

        $request = new DeleteConditionalMenuRequest();
        $request->setMenuId($menuId);

        $result = $request->getMenuId();
        $options = $request->getRequestOptions();

        /** @var array{json: array{menuid: string}} $options */
        $this->assertEquals('', $result);
        $this->assertEquals('', $options['json']['menuid']);
    }

    public function testLongMenuIdShouldWork(): void
    {
        $menuId = str_repeat('a', 100); // 100个字符的长ID

        $request = new DeleteConditionalMenuRequest();
        $request->setMenuId($menuId);

        $result = $request->getMenuId();
        $options = $request->getRequestOptions();

        /** @var array{json: array{menuid: string}} $options */
        $this->assertEquals($menuId, $result);
        $this->assertEquals($menuId, $options['json']['menuid']);
        $this->assertEquals(100, strlen($result));
    }

    public function testMenuIdWithUnicodeCharacters(): void
    {
        $menuId = 'menu_测试_🚀';

        $request = new DeleteConditionalMenuRequest();
        $request->setMenuId($menuId);

        $result = $request->getMenuId();
        $options = $request->getRequestOptions();

        /** @var array{json: array{menuid: string}} $options */
        $this->assertEquals($menuId, $result);
        $this->assertEquals($menuId, $options['json']['menuid']);
    }

    public function testMethodChainingShouldNotBeApplicable(): void
    {
        // 这个类只有setter方法，不支持方法链式调用
        $menuId = 'chain_test';
        $request = new DeleteConditionalMenuRequest();

        // setMenuId方法返回void，不支持链式调用
        $request->setMenuId($menuId);

        $this->assertEquals($menuId, $request->getMenuId());
    }

    public function testRequestConsistencyWithApiDocumentation(): void
    {
        // 验证请求格式与微信API文档一致
        $menuId = 'official_menu_id';
        $request = new DeleteConditionalMenuRequest();
        $request->setMenuId($menuId);

        // 验证URL
        $this->assertEquals('https://api.weixin.qq.com/cgi-bin/menu/delconditional', $request->getRequestPath());

        // 验证HTTP方法
        $this->assertEquals('POST', $request->getRequestMethod());

        // 验证请求体格式
        $options = $request->getRequestOptions();
        /** @var array{json: array{menuid: string}} $options */
        $this->assertArrayHasKey('json', $options);
        $this->assertArrayHasKey('menuid', $options['json']);
        $this->assertEquals($menuId, $options['json']['menuid']);
    }
}
