<?php

declare(strict_types=1);

namespace WechatOfficialAccountMenuBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Entity\MenuButtonVersion;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;
use WechatOfficialAccountMenuBundle\Enum\MenuType;
use WechatOfficialAccountMenuBundle\Enum\MenuVersionStatus;

/**
 * @internal
 */
#[CoversClass(MenuVersion::class)]
final class MenuVersionTest extends AbstractEntityTestCase
{
    protected function createEntity(): MenuVersion
    {
        return new MenuVersion();
    }

    public function testConstruct(): void
    {
        $menuVersion = new MenuVersion();

        self::assertCount(0, $menuVersion->getButtons());
        self::assertSame(MenuVersionStatus::DRAFT, $menuVersion->getStatus());
    }

    public function testToString(): void
    {
        $menuVersion = new MenuVersion();
        $menuVersion->setVersion('1.0.0');
        $menuVersion->setStatus(MenuVersionStatus::PUBLISHED);

        $result = (string) $menuVersion;

        self::assertSame('v1.0.0 - 已发布', $result);
    }

    public function testBasicGettersAndSetters(): void
    {
        $account = new Account();
        $menuVersion = new MenuVersion();

        $menuVersion->setAccount($account);
        self::assertSame($account, $menuVersion->getAccount());

        $menuVersion->setVersion('1.0.0');
        self::assertSame('1.0.0', $menuVersion->getVersion());

        $menuVersion->setDescription('测试版本');
        self::assertSame('测试版本', $menuVersion->getDescription());

        $menuVersion->setStatus(MenuVersionStatus::PUBLISHED);
        self::assertSame(MenuVersionStatus::PUBLISHED, $menuVersion->getStatus());

        $publishedAt = new \DateTimeImmutable('2024-01-01 12:00:00');
        $menuVersion->setPublishedAt($publishedAt);
        self::assertSame($publishedAt, $menuVersion->getPublishedAt());

        $menuVersion->setPublishedBy('admin');
        self::assertSame('admin', $menuVersion->getPublishedBy());

        $copiedFrom = new MenuVersion();
        $menuVersion->setCopiedFrom($copiedFrom);
        self::assertSame($copiedFrom, $menuVersion->getCopiedFrom());

        $snapshot = ['button' => []];
        $menuVersion->setMenuSnapshot($snapshot);
        self::assertSame($snapshot, $menuVersion->getMenuSnapshot());
    }

    public function testButtonsRelationship(): void
    {
        $menuVersion = new MenuVersion();
        $button1 = new MenuButtonVersion();
        $button2 = new MenuButtonVersion();

        $menuVersion->addButton($button1);
        $menuVersion->addButton($button2);

        self::assertCount(2, $menuVersion->getButtons());
        self::assertTrue($menuVersion->getButtons()->contains($button1));
        self::assertTrue($menuVersion->getButtons()->contains($button2));
        self::assertSame($menuVersion, $button1->getVersion());
        self::assertSame($menuVersion, $button2->getVersion());

        // Test adding same button twice
        $menuVersion->addButton($button1);
        self::assertCount(2, $menuVersion->getButtons());

        $menuVersion->removeButton($button1);
        self::assertCount(1, $menuVersion->getButtons());
        self::assertFalse($menuVersion->getButtons()->contains($button1));
        self::assertNull($button1->getVersion());
    }

    public function testGetRootButtons(): void
    {
        $menuVersion = new MenuVersion();

        $root1 = new MenuButtonVersion();
        $root1->setName('根菜单1');
        $menuVersion->addButton($root1);

        $root2 = new MenuButtonVersion();
        $root2->setName('根菜单2');
        $menuVersion->addButton($root2);

        $child = new MenuButtonVersion();
        $child->setName('子菜单');
        $child->setParent($root1);
        $menuVersion->addButton($child);

        $rootButtons = $menuVersion->getRootButtons();

        self::assertCount(2, $rootButtons);
        self::assertTrue($rootButtons->contains($root1));
        self::assertTrue($rootButtons->contains($root2));
        self::assertFalse($rootButtons->contains($child));
    }

    public function testStatusChecks(): void
    {
        $menuVersion = new MenuVersion();

        $menuVersion->setStatus(MenuVersionStatus::DRAFT);
        self::assertTrue($menuVersion->isDraft());
        self::assertFalse($menuVersion->isPublished());
        self::assertFalse($menuVersion->isArchived());

        $menuVersion->setStatus(MenuVersionStatus::PUBLISHED);
        self::assertFalse($menuVersion->isDraft());
        self::assertTrue($menuVersion->isPublished());
        self::assertFalse($menuVersion->isArchived());

        $menuVersion->setStatus(MenuVersionStatus::ARCHIVED);
        self::assertFalse($menuVersion->isDraft());
        self::assertFalse($menuVersion->isPublished());
        self::assertTrue($menuVersion->isArchived());
    }

    public function testPublish(): void
    {
        $menuVersion = new MenuVersion();
        $menuVersion->setStatus(MenuVersionStatus::DRAFT);

        $menuVersion->publish('admin');

        self::assertSame(MenuVersionStatus::PUBLISHED, $menuVersion->getStatus());
        self::assertInstanceOf(\DateTimeImmutable::class, $menuVersion->getPublishedAt());
        self::assertSame('admin', $menuVersion->getPublishedBy());
    }

    public function testArchive(): void
    {
        $menuVersion = new MenuVersion();
        $menuVersion->setStatus(MenuVersionStatus::PUBLISHED);

        $menuVersion->archive();

        self::assertSame(MenuVersionStatus::ARCHIVED, $menuVersion->getStatus());
    }

    public function testToWechatFormat(): void
    {
        $menuVersion = new MenuVersion();

        $root1 = new MenuButtonVersion();
        $root1->setName('菜单1');
        $root1->setType(MenuType::CLICK);
        $root1->setClickKey('KEY_1');
        $menuVersion->addButton($root1);

        $root2 = new MenuButtonVersion();
        $root2->setName('菜单2');
        $menuVersion->addButton($root2);

        $child = new MenuButtonVersion();
        $child->setName('子菜单');
        $child->setType(MenuType::VIEW);
        $child->setUrl('https://example.com');
        $root2->addChild($child); // 使用 addChild 而不是 setParent
        $menuVersion->addButton($child);

        $result = $menuVersion->toWechatFormat();

        self::assertSame([
            'button' => [
                [
                    'type' => 'click',
                    'name' => '菜单1',
                    'key' => 'KEY_1',
                ],
                [
                    'name' => '菜单2',
                    'sub_button' => [
                        [
                            'type' => 'view',
                            'name' => '子菜单',
                            'url' => 'https://example.com',
                        ],
                    ],
                ],
            ],
        ], $result);
    }

    #[DataProvider('provideVersionNumbers')]
    public function testGenerateNextVersion(string $current, string $expected): void
    {
        $result = MenuVersion::generateNextVersion($current);

        self::assertSame($expected, $result);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideVersionNumbers(): iterable
    {
        yield 'simple increment' => ['1.0.0', '1.0.1'];
        yield 'double digit' => ['1.0.9', '1.0.10'];
        yield 'single version' => ['1', '2'];
        yield 'two part version' => ['1.0', '1.1'];
        yield 'complex version' => ['2.3.4.5', '2.3.4.6'];
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'version' => ['version', '1.0.0'];
        yield 'description' => ['description', '测试版本'];
        yield 'status' => ['status', MenuVersionStatus::PUBLISHED];
        yield 'publishedAt' => ['publishedAt', new \DateTimeImmutable('2024-01-01 12:00:00')];
        yield 'publishedBy' => ['publishedBy', 'admin'];
        yield 'menuSnapshot' => ['menuSnapshot', ['button' => []]];
    }
}
