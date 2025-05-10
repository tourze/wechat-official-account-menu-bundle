<?php

namespace WechatOfficialAccountMenuBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Entity\MenuButton;
use WechatOfficialAccountMenuBundle\Enum\MenuType;

class MenuButtonTest extends TestCase
{
    private MenuButton $menuButton;
    private Account $account;

    protected function setUp(): void
    {
        $this->menuButton = new MenuButton();
        
        $this->account = $this->createMock(Account::class);
        $this->account->method('getId')->willReturn(123456);
    }

    public function testConstructor_shouldInitializeCollection(): void
    {
        // 验证构造函数正确初始化了子菜单集合
        $this->assertEmpty($this->menuButton->getChildren());
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $this->menuButton->getChildren());
    }

    public function testSetGetName_shouldWorkCorrectly(): void
    {
        $name = '测试菜单';
        $this->menuButton->setName($name);
        
        $this->assertEquals($name, $this->menuButton->getName());
    }

    public function testSetGetAccount_shouldWorkCorrectly(): void
    {
        $this->menuButton->setAccount($this->account);
        
        $this->assertSame($this->account, $this->menuButton->getAccount());
    }

    public function testSetGetType_shouldWorkCorrectly(): void
    {
        $type = MenuType::VIEW;
        $this->menuButton->setType($type);
        
        $this->assertSame($type, $this->menuButton->getType());
    }

    public function testSetGetClickKey_shouldWorkCorrectly(): void
    {
        $key = 'test_key';
        $this->menuButton->setClickKey($key);
        
        $this->assertEquals($key, $this->menuButton->getClickKey());
    }

    public function testSetGetUrl_shouldWorkCorrectly(): void
    {
        $url = 'https://example.com';
        $this->menuButton->setUrl($url);
        
        $this->assertEquals($url, $this->menuButton->getUrl());
    }

    public function testSetGetAppId_shouldWorkCorrectly(): void
    {
        $appId = 'wx123456789';
        $this->menuButton->setAppId($appId);
        
        $this->assertEquals($appId, $this->menuButton->getAppId());
    }

    public function testSetGetPagePath_shouldWorkCorrectly(): void
    {
        $pagePath = 'pages/index/index';
        $this->menuButton->setPagePath($pagePath);
        
        $this->assertEquals($pagePath, $this->menuButton->getPagePath());
    }

    public function testGetTitle_shouldReturnName(): void
    {
        $name = '测试菜单';
        $this->menuButton->setName($name);
        
        // getTitle 方法应返回 getName 相同的值
        $this->assertEquals($name, $this->menuButton->getTitle());
        $this->assertEquals($this->menuButton->getName(), $this->menuButton->getTitle());
    }

    public function testToString_withoutId_shouldReturnEmptyString(): void
    {
        // ID为null时应返回空字符串
        $this->assertEquals('', (string)$this->menuButton);
    }

    public function testToString_withId_shouldReturnFormattedString(): void
    {
        // 使用反射设置私有属性ID
        $reflection = new \ReflectionClass($this->menuButton);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->menuButton, '123');
        
        $this->menuButton->setName('测试菜单');
        
        $this->assertEquals('#123 测试菜单', (string)$this->menuButton);
    }

    public function testToSelectItem_shouldReturnCorrectArray(): void
    {
        // 使用反射设置私有属性ID
        $reflection = new \ReflectionClass($this->menuButton);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->menuButton, '123');
        
        $this->menuButton->setName('测试菜单');
        
        $result = $this->menuButton->toSelectItem();
        
        // 首先断言数组包含必要的键
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertArrayHasKey('text', $result);
        
        // 然后断言关键值是否正确
        $this->assertEquals('123', $result['value']);
        $this->assertEquals('测试菜单', $result['label']);
        $this->assertEquals('测试菜单', $result['text']);
    }

    public function testToWechatFormat_withoutChildren_shouldReturnCorrectFormat(): void
    {
        $this->menuButton->setName('测试菜单');
        $this->menuButton->setType(MenuType::VIEW);
        $this->menuButton->setUrl('https://example.com');
        
        $expected = [
            'name' => '测试菜单',
            'type' => 'view',
            'url' => 'https://example.com',
        ];
        
        $this->assertEquals($expected, $this->menuButton->toWechatFormat());
    }

    public function testToWechatFormat_withClickType_shouldIncludeKey(): void
    {
        $this->menuButton->setName('点击菜单');
        $this->menuButton->setType(MenuType::CLICK);
        $this->menuButton->setClickKey('click_key_value');
        
        $expected = [
            'name' => '点击菜单',
            'type' => 'click',
            'key' => 'click_key_value',
        ];
        
        $this->assertEquals($expected, $this->menuButton->toWechatFormat());
    }

    public function testToWechatFormat_withMiniProgramType_shouldIncludeAppIdAndPagePath(): void
    {
        $this->menuButton->setName('小程序菜单');
        $this->menuButton->setType(MenuType::MINI_PROGRAM);
        $this->menuButton->setAppId('wx123456789');
        $this->menuButton->setPagePath('pages/index/index');
        $this->menuButton->setUrl('https://example.com'); // 兜底链接
        
        $expected = [
            'name' => '小程序菜单',
            'type' => 'miniprogram',
            'url' => 'https://example.com',
            'appid' => 'wx123456789',
            'pagepath' => 'pages/index/index',
        ];
        
        $this->assertEquals($expected, $this->menuButton->toWechatFormat());
    }
    
    public function testEnsureSameAccount_shouldDoNothing_whenNoParent(): void
    {
        // 没有父菜单时不应抛出异常
        $this->menuButton->setAccount($this->account);
        $this->menuButton->ensureSameAccount();
        $this->expectNotToPerformAssertions();
    }
} 