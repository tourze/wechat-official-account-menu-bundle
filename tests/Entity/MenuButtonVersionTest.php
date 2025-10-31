<?php

declare(strict_types=1);

namespace WechatOfficialAccountMenuBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Entity\MenuButton;
use WechatOfficialAccountMenuBundle\Entity\MenuButtonVersion;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;
use WechatOfficialAccountMenuBundle\Enum\MenuType;

/**
 * @internal
 */
#[CoversClass(MenuButtonVersion::class)]
final class MenuButtonVersionTest extends AbstractEntityTestCase
{
    protected function createEntity(): MenuButtonVersion
    {
        return new MenuButtonVersion();
    }

    public function testConstruct(): void
    {
        $buttonVersion = new MenuButtonVersion();

        self::assertCount(0, $buttonVersion->getChildren());
        self::assertSame(0, $buttonVersion->getPosition());
        self::assertTrue($buttonVersion->isEnabled());
    }

    public function testToString(): void
    {
        $buttonVersion = new MenuButtonVersion();
        $buttonVersion->setName('首页');

        $reflection = new \ReflectionClass($buttonVersion);
        $property = $reflection->getProperty('id');
        $property->setValue($buttonVersion, '123');

        self::assertSame('#123 首页', (string) $buttonVersion);
    }

    public function testBasicGettersAndSetters(): void
    {
        $menuVersion = new MenuVersion();
        $buttonVersion = new MenuButtonVersion();

        $buttonVersion->setVersion($menuVersion);
        self::assertSame($menuVersion, $buttonVersion->getVersion());

        $buttonVersion->setType(MenuType::CLICK);
        self::assertSame(MenuType::CLICK, $buttonVersion->getType());

        $buttonVersion->setName('测试菜单');
        self::assertSame('测试菜单', $buttonVersion->getName());

        $buttonVersion->setClickKey('TEST_KEY');
        self::assertSame('TEST_KEY', $buttonVersion->getClickKey());

        $buttonVersion->setUrl('https://example.com');
        self::assertSame('https://example.com', $buttonVersion->getUrl());

        $buttonVersion->setAppId('wx123456');
        self::assertSame('wx123456', $buttonVersion->getAppId());

        $buttonVersion->setPagePath('pages/index/index');
        self::assertSame('pages/index/index', $buttonVersion->getPagePath());

        $buttonVersion->setMediaId('media_123');
        self::assertSame('media_123', $buttonVersion->getMediaId());

        $buttonVersion->setPosition(10);
        self::assertSame(10, $buttonVersion->getPosition());

        $buttonVersion->setEnabled(false);
        self::assertFalse($buttonVersion->isEnabled());

        $buttonVersion->setOriginalButtonId('original_123');
        self::assertSame('original_123', $buttonVersion->getOriginalButtonId());
    }

    public function testParentChildRelationship(): void
    {
        $parent = new MenuButtonVersion();
        $child1 = new MenuButtonVersion();
        $child2 = new MenuButtonVersion();

        $parent->addChild($child1);
        $parent->addChild($child2);

        self::assertCount(2, $parent->getChildren());
        self::assertTrue($parent->getChildren()->contains($child1));
        self::assertTrue($parent->getChildren()->contains($child2));
        self::assertSame($parent, $child1->getParent());
        self::assertSame($parent, $child2->getParent());

        // Test adding same child twice
        $parent->addChild($child1);
        self::assertCount(2, $parent->getChildren());

        $parent->removeChild($child1);
        self::assertCount(1, $parent->getChildren());
        self::assertFalse($parent->getChildren()->contains($child1));
        self::assertNull($child1->getParent());
    }

    public function testToWechatFormatForParentMenu(): void
    {
        $parent = new MenuButtonVersion();
        $parent->setName('父菜单');

        $child1 = new MenuButtonVersion();
        $child1->setName('子菜单1');
        $child1->setType(MenuType::CLICK);
        $child1->setClickKey('SUB_1');
        $child1->setEnabled(true);

        $child2 = new MenuButtonVersion();
        $child2->setName('子菜单2');
        $child2->setType(MenuType::VIEW);
        $child2->setUrl('https://example.com');
        $child2->setEnabled(false);

        $parent->addChild($child1);
        $parent->addChild($child2);

        $result = $parent->toWechatFormat();

        self::assertSame([
            'name' => '父菜单',
            'sub_button' => [
                [
                    'type' => 'click',
                    'name' => '子菜单1',
                    'key' => 'SUB_1',
                ],
            ],
        ], $result);
    }

    public function testToWechatFormatForClickType(): void
    {
        $buttonVersion = new MenuButtonVersion();
        $buttonVersion->setName('测试菜单');
        $buttonVersion->setType(MenuType::CLICK);
        $buttonVersion->setClickKey('TEST_KEY');

        $result = $buttonVersion->toWechatFormat();

        self::assertSame(['type' => 'click', 'name' => '测试菜单', 'key' => 'TEST_KEY'], $result);
    }

    public function testToWechatFormatForViewType(): void
    {
        $buttonVersion = new MenuButtonVersion();
        $buttonVersion->setName('测试菜单');
        $buttonVersion->setType(MenuType::VIEW);
        $buttonVersion->setUrl('https://example.com');

        $result = $buttonVersion->toWechatFormat();

        self::assertSame(['type' => 'view', 'name' => '测试菜单', 'url' => 'https://example.com'], $result);
    }

    public function testToWechatFormatForMiniProgramType(): void
    {
        $buttonVersion = new MenuButtonVersion();
        $buttonVersion->setName('测试菜单');
        $buttonVersion->setType(MenuType::MINI_PROGRAM);
        $buttonVersion->setUrl('https://example.com');
        $buttonVersion->setAppId('wx123');
        $buttonVersion->setPagePath('pages/index');

        $result = $buttonVersion->toWechatFormat();

        self::assertSame([
            'type' => 'miniprogram',
            'name' => '测试菜单',
            'url' => 'https://example.com',
            'appid' => 'wx123',
            'pagepath' => 'pages/index',
        ], $result);
    }

    public function testToWechatFormatForScanCodePushType(): void
    {
        $buttonVersion = new MenuButtonVersion();
        $buttonVersion->setName('测试菜单');
        $buttonVersion->setType(MenuType::SCAN_CODE_PUSH);
        $buttonVersion->setClickKey('SCAN_KEY');

        $result = $buttonVersion->toWechatFormat();

        self::assertSame(['type' => 'scancode_push', 'name' => '测试菜单', 'key' => 'SCAN_KEY'], $result);
    }

    public function testCreateFromMenuButton(): void
    {
        $account = new Account();

        $menuButton = new MenuButton();
        $menuButton->setAccount($account);
        $menuButton->setType(MenuType::CLICK);
        $menuButton->setName('原始菜单');
        $menuButton->setClickKey('ORIGINAL_KEY');
        $menuButton->setUrl('https://example.com');
        $menuButton->setAppId('wx123');
        $menuButton->setPagePath('pages/test');
        $menuButton->setMediaId('media_123');

        $reflection = new \ReflectionClass($menuButton);
        $property = $reflection->getProperty('id');
        $property->setValue($menuButton, 'button_123');

        $buttonVersion = MenuButtonVersion::createFromMenuButton($menuButton);

        self::assertSame(MenuType::CLICK, $buttonVersion->getType());
        self::assertSame('原始菜单', $buttonVersion->getName());
        self::assertSame('ORIGINAL_KEY', $buttonVersion->getClickKey());
        self::assertSame('https://example.com', $buttonVersion->getUrl());
        self::assertSame('wx123', $buttonVersion->getAppId());
        self::assertSame('pages/test', $buttonVersion->getPagePath());
        self::assertSame('media_123', $buttonVersion->getMediaId());
        self::assertSame('button_123', $buttonVersion->getOriginalButtonId());
    }

    public function testCreateFromMenuButtonWithNullValues(): void
    {
        $account = new Account();

        $menuButton = new MenuButton();
        $menuButton->setAccount($account);

        $buttonVersion = MenuButtonVersion::createFromMenuButton($menuButton);

        self::assertNull($buttonVersion->getType());
        self::assertNull($buttonVersion->getName());
        self::assertNull($buttonVersion->getClickKey());
        self::assertNull($buttonVersion->getUrl());
        self::assertNull($buttonVersion->getAppId());
        self::assertNull($buttonVersion->getPagePath());
        self::assertNull($buttonVersion->getMediaId());
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'type' => ['type', MenuType::CLICK];
        yield 'name' => ['name', '测试菜单'];
        yield 'clickKey' => ['clickKey', 'TEST_KEY'];
        yield 'url' => ['url', 'https://example.com'];
        yield 'appId' => ['appId', 'wx123456'];
        yield 'pagePath' => ['pagePath', 'pages/index/index'];
        yield 'mediaId' => ['mediaId', 'media_123'];
        yield 'position' => ['position', 10];
        yield 'enabled' => ['enabled', false];
        yield 'originalButtonId' => ['originalButtonId', 'button_123'];
    }
}
