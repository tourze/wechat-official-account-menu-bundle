<?php

namespace WechatOfficialAccountMenuBundle\Tests\Request\Menu;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use WechatOfficialAccountBundle\Request\WithAccountRequest;
use WechatOfficialAccountMenuBundle\Request\Menu\AddConditionalMenuRequest;

/**
 * @internal
 */
#[CoversClass(AddConditionalMenuRequest::class)]
final class AddConditionalMenuRequestTest extends RequestTestCase
{
    public function testRequestShouldExtendWithAccountRequest(): void
    {
        $request = new AddConditionalMenuRequest();

        $this->assertInstanceOf(WithAccountRequest::class, $request);
    }

    public function testGetRequestPathShouldReturnCorrectPath(): void
    {
        $request = new AddConditionalMenuRequest();

        $path = $request->getRequestPath();

        $this->assertEquals('https://api.weixin.qq.com/cgi-bin/menu/addconditional', $path);
    }

    public function testGetRequestMethodShouldReturnPost(): void
    {
        $request = new AddConditionalMenuRequest();

        $method = $request->getRequestMethod();

        $this->assertEquals('POST', $method);
    }

    public function testSetMenuDataShouldSetMenuData(): void
    {
        $menuData = [
            'button' => [
                [
                    'name' => 'Test Menu',
                    'type' => 'click',
                    'key' => 'test_key',
                ],
            ],
        ];

        $request = new AddConditionalMenuRequest();
        $request->setMenuData($menuData);

        $this->assertEquals($menuData, $request->getMenuData());
    }

    public function testGetMenuDataShouldReturnMenuData(): void
    {
        $menuData = [
            'button' => [
                [
                    'name' => 'Test Menu',
                    'type' => 'view',
                    'url' => 'https://example.com',
                ],
            ],
        ];

        $request = new AddConditionalMenuRequest();
        $request->setMenuData($menuData);

        $result = $request->getMenuData();

        $this->assertIsArray($result);
        $this->assertEquals($menuData, $result);
    }

    public function testSetMatchRuleShouldSetMatchRule(): void
    {
        $matchRule = [
            'group_id' => '1',
            'sex' => '1',
            'country' => 'CN',
            'province' => 'GD',
            'city' => 'SZ',
        ];

        $request = new AddConditionalMenuRequest();
        $request->setMatchRule($matchRule);

        $this->assertEquals($matchRule, $request->getMatchRule());
    }

    public function testGetMatchRuleShouldReturnMatchRule(): void
    {
        $matchRule = [
            'tag_id' => '101',
            'client_platform_type' => '1',
        ];

        $request = new AddConditionalMenuRequest();
        $request->setMatchRule($matchRule);

        $result = $request->getMatchRule();

        $this->assertIsArray($result);
        $this->assertEquals($matchRule, $result);
    }

    public function testGetRequestOptionsShouldMergeMenuDataAndMatchRule(): void
    {
        $menuData = [
            'button' => [
                ['name' => 'Menu 1', 'type' => 'click', 'key' => 'key1'],
            ],
        ];

        $matchRule = [
            'group_id' => '2',
            'sex' => '2',
        ];

        $request = new AddConditionalMenuRequest();
        $request->setMenuData($menuData);
        $request->setMatchRule($matchRule);

        $options = $request->getRequestOptions();

        /** @var array{json: array{button: array<mixed>, matchrule: array<mixed>}} $options */
        $this->assertIsArray($options);
        $this->assertArrayHasKey('json', $options);

        $jsonData = $options['json'];
        $this->assertArrayHasKey('button', $jsonData);
        $this->assertArrayHasKey('matchrule', $jsonData);

        $this->assertEquals($menuData['button'], $jsonData['button']);
        $this->assertEquals($matchRule, $jsonData['matchrule']);
    }

    public function testGetRequestOptionsWithComplexMenuStructure(): void
    {
        $menuData = [
            'button' => [
                [
                    'name' => 'Parent Menu',
                    'sub_button' => [
                        ['name' => 'Sub 1', 'type' => 'click', 'key' => 'sub1'],
                        ['name' => 'Sub 2', 'type' => 'view', 'url' => 'https://example.com'],
                    ],
                ],
                [
                    'name' => 'Single Menu',
                    'type' => 'scancode_push',
                    'key' => 'scan_key',
                ],
            ],
        ];

        $matchRule = [
            'tag_id' => '100',
            'client_platform_type' => '2',
            'language' => 'zh_CN',
        ];

        $request = new AddConditionalMenuRequest();
        $request->setMenuData($menuData);
        $request->setMatchRule($matchRule);

        $options = $request->getRequestOptions();
        /** @var array{json: array{button: array<int, array{name: string, sub_button?: array<mixed>}>, matchrule: array<mixed>}} $options */
        $jsonData = $options['json'];

        // 验证菜单结构完整性
        $this->assertCount(2, $jsonData['button']);
        $this->assertEquals('Parent Menu', $jsonData['button'][0]['name']);
        $this->assertArrayHasKey('sub_button', $jsonData['button'][0]);
        /** @var array<mixed> $subButton */
        $subButton = $jsonData['button'][0]['sub_button'];
        $this->assertCount(2, $subButton);

        // 验证匹配规则
        $this->assertEquals($matchRule, $jsonData['matchrule']);
    }

    public function testEmptyMenuDataAndMatchRuleShouldWork(): void
    {
        $request = new AddConditionalMenuRequest();
        $request->setMenuData([]);
        $request->setMatchRule([]);

        $options = $request->getRequestOptions();
        /** @var array{json: array{matchrule: array<mixed>}} $options */
        $jsonData = $options['json'];

        $this->assertArrayHasKey('matchrule', $jsonData);
        $this->assertEquals([], $jsonData['matchrule']);
    }

    public function testMatchRuleKeyInRequestOptionsShouldBeLowercase(): void
    {
        $menuData = ['button' => []];
        $matchRule = ['group_id' => '1'];

        $request = new AddConditionalMenuRequest();
        $request->setMenuData($menuData);
        $request->setMatchRule($matchRule);

        $options = $request->getRequestOptions();
        /** @var array{json: array<string, mixed>} $options */
        $jsonData = $options['json'];

        // 验证是小写的 "matchrule" 而不是 "matchRule"
        $this->assertArrayHasKey('matchrule', $jsonData);
        $this->assertArrayNotHasKey('matchRule', $jsonData);
        $this->assertArrayNotHasKey('match_rule', $jsonData);
    }
}
