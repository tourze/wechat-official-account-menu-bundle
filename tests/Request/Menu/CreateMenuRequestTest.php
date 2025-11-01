<?php

namespace WechatOfficialAccountMenuBundle\Tests\Request\Menu;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use WechatOfficialAccountBundle\Request\WithAccountRequest;
use WechatOfficialAccountMenuBundle\Request\Menu\CreateMenuRequest;

/**
 * @internal
 */
#[CoversClass(CreateMenuRequest::class)]
final class CreateMenuRequestTest extends RequestTestCase
{
    public function testRequestShouldExtendWithAccountRequest(): void
    {
        $request = new CreateMenuRequest();

        $this->assertInstanceOf(WithAccountRequest::class, $request);
    }

    public function testGetRequestPathShouldReturnCorrectPath(): void
    {
        $request = new CreateMenuRequest();

        $path = $request->getRequestPath();

        $this->assertEquals('https://api.weixin.qq.com/cgi-bin/menu/create', $path);
    }

    public function testGetRequestMethodShouldReturnPost(): void
    {
        $request = new CreateMenuRequest();

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

        $request = new CreateMenuRequest();
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

        $request = new CreateMenuRequest();
        $request->setMenuData($menuData);

        $result = $request->getMenuData();

        $this->assertIsArray($result);
        $this->assertEquals($menuData, $result);
    }

    public function testGetRequestOptionsShouldReturnJsonWithMenuData(): void
    {
        $menuData = [
            'button' => [
                [
                    'name' => 'Menu 1',
                    'type' => 'click',
                    'key' => 'key1',
                ],
                [
                    'name' => 'Menu 2',
                    'type' => 'view',
                    'url' => 'https://example.com',
                ],
            ],
        ];

        $request = new CreateMenuRequest();
        $request->setMenuData($menuData);

        $options = $request->getRequestOptions();

        /** @var array{json: array<mixed>} $options */
        $this->assertIsArray($options);
        $this->assertArrayHasKey('json', $options);
        $this->assertEquals($menuData, $options['json']);
    }

    public function testGetRequestOptionsWithComplexMenuStructure(): void
    {
        $menuData = [
            'button' => [
                [
                    'name' => 'Parent Menu',
                    'sub_button' => [
                        [
                            'name' => 'Sub Menu 1',
                            'type' => 'click',
                            'key' => 'sub1',
                        ],
                        [
                            'name' => 'Sub Menu 2',
                            'type' => 'view',
                            'url' => 'https://example.com/page2',
                        ],
                        [
                            'name' => 'Sub Menu 3',
                            'type' => 'miniprogram',
                            'url' => 'https://example.com',
                            'appid' => 'wx123456789',
                            'pagepath' => 'pages/index',
                        ],
                    ],
                ],
                [
                    'name' => 'Single Menu',
                    'type' => 'scancode_waitmsg',
                    'key' => 'scan_key',
                ],
                [
                    'name' => 'Photo Menu',
                    'type' => 'pic_sysphoto',
                    'key' => 'photo_key',
                ],
            ],
        ];

        $request = new CreateMenuRequest();
        $request->setMenuData($menuData);

        $options = $request->getRequestOptions();
        /** @var array{json: array{button: array<int, array<string, mixed>>}} $options */
        $jsonData = $options['json'];

        // 验证菜单结构完整性
        $this->assertCount(3, $jsonData['button']);

        // 验证父菜单
        /** @var array{name: string, sub_button: array<int, array<string, mixed>>} $parentMenu */
        $parentMenu = $jsonData['button'][0];
        $this->assertEquals('Parent Menu', $parentMenu['name']);
        $this->assertArrayHasKey('sub_button', $parentMenu);
        $this->assertCount(3, $parentMenu['sub_button']);

        // 验证子菜单
        $subButtons = $parentMenu['sub_button'];
        /** @var array{name: string, type: string, key: string} $subMenu1 */
        $subMenu1 = $subButtons[0];
        $this->assertEquals('Sub Menu 1', $subMenu1['name']);
        $this->assertEquals('click', $subMenu1['type']);
        $this->assertEquals('sub1', $subMenu1['key']);

        /** @var array{name: string, type: string, url: string} $subMenu2 */
        $subMenu2 = $subButtons[1];
        $this->assertEquals('Sub Menu 2', $subMenu2['name']);
        $this->assertEquals('view', $subMenu2['type']);
        $this->assertEquals('https://example.com/page2', $subMenu2['url']);

        // 验证小程序菜单
        /** @var array{name: string, type: string, appid: string, pagepath: string} $subMenu3 */
        $subMenu3 = $subButtons[2];
        $this->assertEquals('Sub Menu 3', $subMenu3['name']);
        $this->assertEquals('miniprogram', $subMenu3['type']);
        $this->assertEquals('wx123456789', $subMenu3['appid']);
        $this->assertEquals('pages/index', $subMenu3['pagepath']);

        // 验证单独菜单
        /** @var array{name: string, type: string, key: string} $singleMenu */
        $singleMenu = $jsonData['button'][1];
        $this->assertEquals('Single Menu', $singleMenu['name']);
        $this->assertEquals('scancode_waitmsg', $singleMenu['type']);
        $this->assertEquals('scan_key', $singleMenu['key']);

        /** @var array{name: string, type: string, key: string} $photoMenu */
        $photoMenu = $jsonData['button'][2];
        $this->assertEquals('Photo Menu', $photoMenu['name']);
        $this->assertEquals('pic_sysphoto', $photoMenu['type']);
        $this->assertEquals('photo_key', $photoMenu['key']);
    }

    public function testEmptyMenuDataShouldWork(): void
    {
        $request = new CreateMenuRequest();
        $request->setMenuData([]);

        $options = $request->getRequestOptions();

        /** @var array{json: array<mixed>} $options */
        $this->assertIsArray($options);
        $this->assertArrayHasKey('json', $options);
        $this->assertEquals([], $options['json']);
    }

    public function testMenuDataWithDifferentButtonTypes(): void
    {
        $menuData = [
            'button' => [
                [
                    'name' => 'View URL',
                    'type' => 'view',
                    'url' => 'https://example.com',
                ],
                [
                    'name' => 'Click Event',
                    'type' => 'click',
                    'key' => 'CLICK_KEY',
                ],
                [
                    'name' => 'Scan QR',
                    'type' => 'scancode_push',
                    'key' => 'SCAN_PUSH',
                ],
            ],
        ];

        $request = new CreateMenuRequest();
        $request->setMenuData($menuData);

        $options = $request->getRequestOptions();
        /** @var array{json: array{button: array<int, array<string, mixed>>}} $options */
        $buttons = $options['json']['button'];

        $this->assertCount(3, $buttons);

        // 验证不同类型的按钮
        /** @var array{type: string} $button0 */
        $button0 = $buttons[0];
        $this->assertEquals('view', $button0['type']);
        $this->assertArrayHasKey('url', $button0);
        $this->assertArrayNotHasKey('key', $button0);

        /** @var array{type: string} $button1 */
        $button1 = $buttons[1];
        $this->assertEquals('click', $button1['type']);
        $this->assertArrayHasKey('key', $button1);
        $this->assertArrayNotHasKey('url', $button1);

        /** @var array{type: string} $button2 */
        $button2 = $buttons[2];
        $this->assertEquals('scancode_push', $button2['type']);
        $this->assertArrayHasKey('key', $button2);
        $this->assertArrayNotHasKey('url', $button2);
    }

    public function testRequestOptionsStructureShouldBeConsistent(): void
    {
        $menuData = ['button' => []];

        $request = new CreateMenuRequest();
        $request->setMenuData($menuData);

        $options = $request->getRequestOptions();

        // 验证返回的选项结构
        /** @var array{json: array<mixed>} $options */
        $this->assertIsArray($options);
        $this->assertCount(1, $options);
        $this->assertArrayHasKey('json', $options);
        $this->assertIsArray($options['json']);
    }

    public function testMenuDataWithSpecialCharacters(): void
    {
        $menuData = [
            'button' => [
                [
                    'name' => '中文菜单',
                    'type' => 'click',
                    'key' => 'CHINESE_MENU',
                ],
                [
                    'name' => 'Emoji 😀',
                    'type' => 'view',
                    'url' => 'https://example.com',
                ],
            ],
        ];

        $request = new CreateMenuRequest();
        $request->setMenuData($menuData);

        $options = $request->getRequestOptions();
        /** @var array{json: array{button: array<int, array{name: string}>}} $options */
        $buttons = $options['json']['button'];

        $this->assertEquals('中文菜单', $buttons[0]['name']);
        $this->assertEquals('Emoji 😀', $buttons[1]['name']);
    }
}
