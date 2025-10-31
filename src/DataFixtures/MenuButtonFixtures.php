<?php

namespace WechatOfficialAccountMenuBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Entity\MenuButton;
use WechatOfficialAccountMenuBundle\Enum\MenuType;

class MenuButtonFixtures extends Fixture
{
    public const MENU_BUTTON_MAIN = 'menu_button_main';
    public const MENU_BUTTON_SUB = 'menu_button_sub';
    public const MENU_BUTTON_MINIPROGRAM = 'menu_button_miniprogram';

    public function load(ObjectManager $manager): void
    {
        // 创建测试账户
        $account = new Account();
        $account->setName('Test WeChat Account');
        $account->setAppId('wx_test_app_id_menu');
        $account->setAppSecret('test_app_secret_menu_fixtures');
        $manager->persist($account);

        $mainMenu1 = new MenuButton();
        $mainMenu1->setAccount($account);
        $mainMenu1->setName('主菜单1');
        $mainMenu1->setType(MenuType::CLICK);
        $mainMenu1->setClickKey('main_menu_1');
        $mainMenu1->setPosition(0);
        $mainMenu1->setEnabled(true);

        $subMenu1 = new MenuButton();
        $subMenu1->setAccount($account);
        $subMenu1->setName('子菜单1-1');
        $subMenu1->setType(MenuType::VIEW);
        $subMenu1->setUrl('https://www.baidu.com');
        $subMenu1->setParent($mainMenu1);
        $subMenu1->setPosition(0);
        $subMenu1->setEnabled(true);

        $mainMenu2 = new MenuButton();
        $mainMenu2->setAccount($account);
        $mainMenu2->setName('小程序菜单');
        $mainMenu2->setType(MenuType::MINI_PROGRAM);
        $mainMenu2->setUrl('https://www.baidu.com');
        $mainMenu2->setAppId('wxapp123456789');
        $mainMenu2->setPagePath('pages/index/index');
        $mainMenu2->setPosition(1);
        $mainMenu2->setEnabled(true);

        $manager->persist($mainMenu1);
        $manager->persist($subMenu1);
        $manager->persist($mainMenu2);
        $manager->flush();

        $this->addReference(self::MENU_BUTTON_MAIN, $mainMenu1);
        $this->addReference(self::MENU_BUTTON_SUB, $subMenu1);
        $this->addReference(self::MENU_BUTTON_MINIPROGRAM, $mainMenu2);
    }
}
