<?php

namespace WechatOfficialAccountMenuBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use WechatOfficialAccountMenuBundle\Entity\MenuButtonVersion;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;
use WechatOfficialAccountMenuBundle\Enum\MenuType;

class MenuButtonVersionFixtures extends Fixture implements DependentFixtureInterface
{
    public const MENU_BUTTON_VERSION_MAIN_V1 = 'menu_button_version_main_v1';
    public const MENU_BUTTON_VERSION_SUB_V1 = 'menu_button_version_sub_v1';
    public const MENU_BUTTON_VERSION_MAIN_V2 = 'menu_button_version_main_v2';
    public const MENU_BUTTON_VERSION_SUB_V2 = 'menu_button_version_sub_v2';

    public function load(ObjectManager $manager): void
    {
        $draftVersion = $this->getReference(MenuVersionFixtures::MENU_VERSION_DRAFT, MenuVersion::class);
        $publishedVersion = $this->getReference(MenuVersionFixtures::MENU_VERSION_PUBLISHED, MenuVersion::class);

        $mainButtonV1 = new MenuButtonVersion();
        $mainButtonV1->setVersion($draftVersion);
        $mainButtonV1->setType(MenuType::CLICK);
        $mainButtonV1->setName('首页');
        $mainButtonV1->setClickKey('home_menu');
        $mainButtonV1->setPosition(0);
        $mainButtonV1->setEnabled(true);
        $mainButtonV1->setOriginalButtonId('btn_001');

        $subButtonV1 = new MenuButtonVersion();
        $subButtonV1->setVersion($draftVersion);
        $subButtonV1->setType(MenuType::VIEW);
        $subButtonV1->setName('关于我们');
        $subButtonV1->setUrl('https://www.baidu.com');
        $subButtonV1->setParent($mainButtonV1);
        $subButtonV1->setPosition(0);
        $subButtonV1->setEnabled(true);
        $subButtonV1->setOriginalButtonId('btn_002');

        $mainButtonV2 = new MenuButtonVersion();
        $mainButtonV2->setVersion($publishedVersion);
        $mainButtonV2->setType(MenuType::MINI_PROGRAM);
        $mainButtonV2->setName('商城');
        $mainButtonV2->setUrl('https://www.baidu.com');
        $mainButtonV2->setAppId('wxshop123456');
        $mainButtonV2->setPagePath('pages/shop/index');
        $mainButtonV2->setPosition(0);
        $mainButtonV2->setEnabled(true);
        $mainButtonV2->setOriginalButtonId('btn_003');

        $subButtonV2 = new MenuButtonVersion();
        $subButtonV2->setVersion($publishedVersion);
        $subButtonV2->setType(MenuType::CLICK);
        $subButtonV2->setName('联系客服');
        $subButtonV2->setClickKey('contact_service');
        $subButtonV2->setParent($mainButtonV2);
        $subButtonV2->setPosition(0);
        $subButtonV2->setEnabled(true);
        $subButtonV2->setOriginalButtonId('btn_004');

        $manager->persist($mainButtonV1);
        $manager->persist($subButtonV1);
        $manager->persist($mainButtonV2);
        $manager->persist($subButtonV2);
        $manager->flush();

        $this->addReference(self::MENU_BUTTON_VERSION_MAIN_V1, $mainButtonV1);
        $this->addReference(self::MENU_BUTTON_VERSION_SUB_V1, $subButtonV1);
        $this->addReference(self::MENU_BUTTON_VERSION_MAIN_V2, $mainButtonV2);
        $this->addReference(self::MENU_BUTTON_VERSION_SUB_V2, $subButtonV2);
    }

    public function getDependencies(): array
    {
        return [
            MenuVersionFixtures::class,
        ];
    }
}
