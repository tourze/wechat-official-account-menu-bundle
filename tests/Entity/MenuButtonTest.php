<?php

declare(strict_types=1);

namespace WechatOfficialAccountMenuBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Entity\MenuButton;
use WechatOfficialAccountMenuBundle\Enum\MenuType;
use WechatOfficialAccountMenuBundle\Exception\MenuValidationException;

/**
 * @internal
 */
#[CoversClass(MenuButton::class)]
final class MenuButtonTest extends AbstractEntityTestCase
{
    protected function createEntity(): MenuButton
    {
        return new MenuButton();
    }

    private function createAccount(string $id = '1'): Account
    {
        $account = new Account();
        $reflection = new \ReflectionClass($account);
        $property = $reflection->getProperty('id');
        $property->setValue($account, $id);

        return $account;
    }

    public function testConstruct(): void
    {
        $menuButton = new MenuButton();

        self::assertCount(0, $menuButton->getChildren());
        self::assertSame(0, $menuButton->getPosition());
        self::assertTrue($menuButton->isEnabled());
    }

    public function testToString(): void
    {
        $menuButton = new MenuButton();
        self::assertSame('', (string) $menuButton);

        $menuButton->setName('首页');
        $reflection = new \ReflectionClass($menuButton);
        $property = $reflection->getProperty('id');
        $property->setValue($menuButton, '123');

        self::assertSame('#123 首页', (string) $menuButton);
    }

    public function testBasicGettersAndSetters(): void
    {
        $account = $this->createAccount();
        $menuButton = new MenuButton();

        $menuButton->setAccount($account);
        self::assertSame($account, $menuButton->getAccount());

        $menuButton->setName('测试菜单');
        self::assertSame('测试菜单', $menuButton->getName());
        self::assertSame('测试菜单', $menuButton->getTitle());

        $menuButton->setType(MenuType::CLICK);
        self::assertSame(MenuType::CLICK, $menuButton->getType());

        $menuButton->setClickKey('TEST_KEY');
        self::assertSame('TEST_KEY', $menuButton->getClickKey());

        $menuButton->setUrl('https://example.com');
        self::assertSame('https://example.com', $menuButton->getUrl());

        $menuButton->setAppId('wx123456');
        self::assertSame('wx123456', $menuButton->getAppId());

        $menuButton->setPagePath('pages/index/index');
        self::assertSame('pages/index/index', $menuButton->getPagePath());

        $menuButton->setMediaId('media_123');
        self::assertSame('media_123', $menuButton->getMediaId());

        $menuButton->setPosition(10);
        self::assertSame(10, $menuButton->getPosition());

        $menuButton->setEnabled(false);
        self::assertFalse($menuButton->isEnabled());
    }

    public function testParentChildRelationship(): void
    {
        $parent = new MenuButton();
        $child1 = new MenuButton();
        $child2 = new MenuButton();

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

    public function testEnsureSameAccount(): void
    {
        $account1 = $this->createAccount('1');
        $account2 = $this->createAccount('2');

        $parent = new MenuButton();
        $parent->setAccount($account1);

        $child = new MenuButton();
        $child->setAccount($account2);
        $child->setParent($parent);

        $this->expectException(MenuValidationException::class);
        $this->expectExceptionMessage('请选择跟上级同样的公众号');

        $child->ensureSameAccount();
    }

    public function testEnsureSameAccountWithSameAccount(): void
    {
        $account = $this->createAccount();

        $parent = new MenuButton();
        $parent->setAccount($account);

        $child = new MenuButton();
        $child->setAccount($account);
        $child->setParent($parent);

        $child->ensureSameAccount();
        // No exception thrown - test passes
        self::assertSame($account, $child->getAccount());
    }

    public function testEnsureSameAccountWithNoParent(): void
    {
        $account = $this->createAccount();
        $menuButton = new MenuButton();
        $menuButton->setAccount($account);

        $menuButton->ensureSameAccount();
        // No exception thrown - test passes
        self::assertNull($menuButton->getParent());
    }

    public function testToSelectItem(): void
    {
        $menuButton = new MenuButton();
        $menuButton->setName('测试菜单');
        $reflection = new \ReflectionClass($menuButton);
        $property = $reflection->getProperty('id');
        $property->setValue($menuButton, '123');

        $item = $menuButton->toSelectItem();

        self::assertSame([
            'label' => '测试菜单',
            'text' => '测试菜单',
            'value' => '123',
        ], $item);
    }

    public function testToWechatFormatForParentMenu(): void
    {
        $parent = new MenuButton();
        $parent->setName('父菜单');

        $child1 = new MenuButton();
        $child1->setName('子菜单1');
        $child1->setType(MenuType::CLICK);
        $child1->setClickKey('SUB_1');
        $child1->setEnabled(true);

        $child2 = new MenuButton();
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
        $menuButton = new MenuButton();
        $menuButton->setName('测试菜单');
        $menuButton->setType(MenuType::CLICK);
        $menuButton->setClickKey('TEST_KEY');

        $result = $menuButton->toWechatFormat();

        self::assertSame(['type' => 'click', 'name' => '测试菜单', 'key' => 'TEST_KEY'], $result);
    }

    public function testToWechatFormatForViewType(): void
    {
        $menuButton = new MenuButton();
        $menuButton->setName('测试菜单');
        $menuButton->setType(MenuType::VIEW);
        $menuButton->setUrl('https://example.com');

        $result = $menuButton->toWechatFormat();

        self::assertSame(['type' => 'view', 'name' => '测试菜单', 'url' => 'https://example.com'], $result);
    }

    public function testToWechatFormatForMiniProgramType(): void
    {
        $menuButton = new MenuButton();
        $menuButton->setName('测试菜单');
        $menuButton->setType(MenuType::MINI_PROGRAM);
        $menuButton->setUrl('https://example.com');
        $menuButton->setAppId('wx123');
        $menuButton->setPagePath('pages/index');

        $result = $menuButton->toWechatFormat();

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
        $menuButton = new MenuButton();
        $menuButton->setName('测试菜单');
        $menuButton->setType(MenuType::SCAN_CODE_PUSH);
        $menuButton->setClickKey('SCAN_KEY');

        $result = $menuButton->toWechatFormat();

        self::assertSame(['type' => 'scancode_push', 'name' => '测试菜单', 'key' => 'SCAN_KEY'], $result);
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'name' => ['name', '测试菜单'];
        yield 'type' => ['type', MenuType::CLICK];
        yield 'clickKey' => ['clickKey', 'TEST_KEY'];
        yield 'url' => ['url', 'https://example.com'];
        yield 'appId' => ['appId', 'wx123456'];
        yield 'pagePath' => ['pagePath', 'pages/index/index'];
        yield 'mediaId' => ['mediaId', 'media_123'];
        yield 'position' => ['position', 10];
        yield 'enabled' => ['enabled', false];
    }
}
