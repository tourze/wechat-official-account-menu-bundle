<?php

namespace WechatOfficialAccountMenuBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use WechatOfficialAccountMenuBundle\Entity\MenuButton;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;
use WechatOfficialAccountMenuBundle\Enum\MenuVersionStatus;

class MenuVersionFixtures extends Fixture implements DependentFixtureInterface
{
    public const MENU_VERSION_DRAFT = 'menu_version_draft';
    public const MENU_VERSION_PUBLISHED = 'menu_version_published';
    public const MENU_VERSION_ARCHIVED = 'menu_version_archived';

    public function load(ObjectManager $manager): void
    {
        // 从MenuButtonFixtures获取已创建的Account
        $menuButtonMain = $this->getReference(MenuButtonFixtures::MENU_BUTTON_MAIN, MenuButton::class);
        $account = $menuButtonMain->getAccount();

        $draftVersion = new MenuVersion();
        $draftVersion->setAccount($account);
        $draftVersion->setVersion('1.0.0');
        $draftVersion->setDescription('初始版本草稿');
        $draftVersion->setStatus(MenuVersionStatus::DRAFT);

        $publishedVersion = new MenuVersion();
        $publishedVersion->setAccount($account);
        $publishedVersion->setVersion('1.1.0');
        $publishedVersion->setDescription('已发布的菜单版本');
        $publishedVersion->setStatus(MenuVersionStatus::PUBLISHED);
        $publishedVersion->setPublishedAt(new \DateTimeImmutable('-1 week'));
        $publishedVersion->setPublishedBy('admin');

        $archivedVersion = new MenuVersion();
        $archivedVersion->setAccount($account);
        $archivedVersion->setVersion('0.9.0');
        $archivedVersion->setDescription('已归档的旧版本');
        $archivedVersion->setStatus(MenuVersionStatus::ARCHIVED);

        $manager->persist($draftVersion);
        $manager->persist($publishedVersion);
        $manager->persist($archivedVersion);
        $manager->flush();

        $this->addReference(self::MENU_VERSION_DRAFT, $draftVersion);
        $this->addReference(self::MENU_VERSION_PUBLISHED, $publishedVersion);
        $this->addReference(self::MENU_VERSION_ARCHIVED, $archivedVersion);
    }

    public function getDependencies(): array
    {
        return [
            MenuButtonFixtures::class,
        ];
    }
}
