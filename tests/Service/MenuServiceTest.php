<?php

namespace WechatOfficialAccountMenuBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Entity\MenuButton;
use WechatOfficialAccountMenuBundle\Enum\MenuType;
use WechatOfficialAccountMenuBundle\Exception\MenuValidationException;
use WechatOfficialAccountMenuBundle\Service\MenuService;

/**
 * @internal
 */
#[CoversClass(MenuService::class)]
#[RunTestsInSeparateProcesses]
final class MenuServiceTest extends AbstractIntegrationTestCase
{
    private function getMenuService(): MenuService
    {
        return self::getService(MenuService::class);
    }

    protected function onSetUp(): void        // 清理数据库中的所有数据
    {
        self::cleanDatabase();
    }

    public function testCreateMenuButton(): void
    {
        $account = new Account();
        $account->setName('Test Account');
        $account->setAppId('test_app_id');
        $account->setAppSecret('test_app_secret');

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $data = [
            'name' => 'Test Menu',
            'type' => MenuType::CLICK,
            'clickKey' => 'test_key',
        ];

        $result = $this->getMenuService()->createMenuButton($account, $data);
        $this->assertInstanceOf(MenuButton::class, $result);
        $this->assertSame('Test Menu', $result->getName());
        $this->assertSame(MenuType::CLICK, $result->getType());
        $this->assertSame('test_key', $result->getClickKey());
    }

    public function testUpdateMenuButton(): void
    {
        $account = new Account();
        $account->setName('Test Account');
        $account->setAppId('test_app_id');
        $account->setAppSecret('test_app_secret');

        $menuButton = new MenuButton();
        $menuButton->setAccount($account);
        $menuButton->setName('Original Menu');
        $menuButton->setType(MenuType::CLICK);
        $menuButton->setClickKey('original_key');
        $menuButton->setPosition(0);
        $menuButton->setEnabled(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuButton);
        self::getEntityManager()->flush();

        $data = ['name' => 'Updated Menu'];
        $result = $this->getMenuService()->updateMenuButton($menuButton, $data);

        $this->assertSame($menuButton, $result);
        $this->assertSame('Updated Menu', $result->getName());
    }

    public function testDeleteMenuButton(): void
    {
        $account = new Account();
        $account->setName('Test Account');
        $account->setAppId('test_app_id');
        $account->setAppSecret('test_app_secret');

        $parentButton = new MenuButton();
        $parentButton->setAccount($account);
        $parentButton->setName('Parent Menu');
        $parentButton->setType(MenuType::CLICK);
        $parentButton->setClickKey('parent_key');
        $parentButton->setPosition(0);
        $parentButton->setEnabled(true);

        $childButton = new MenuButton();
        $childButton->setAccount($account);
        $childButton->setName('Child Menu');
        $childButton->setType(MenuType::CLICK);
        $childButton->setClickKey('child_key');
        $childButton->setPosition(0);
        $childButton->setEnabled(true);
        $childButton->setParent($parentButton);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($parentButton);
        self::getEntityManager()->persist($childButton);
        self::getEntityManager()->flush();

        $parentId = $parentButton->getId();
        $childId = $childButton->getId();

        $this->getMenuService()->deleteMenuButton($parentButton);

        // Clear entity manager to force fresh database query
        self::getEntityManager()->clear();

        $repo = self::getEntityManager()->getRepository(MenuButton::class);
        $this->assertNull($repo->find($parentId), 'Parent button should be deleted');
    }

    public function testUpdateMenuPositions(): void
    {
        $account = new Account();
        $account->setName('Test Account');
        $account->setAppId('test_app_id');
        $account->setAppSecret('test_app_secret');

        $button1 = new MenuButton();
        $button1->setAccount($account);
        $button1->setName('Button 1');
        $button1->setType(MenuType::CLICK);
        $button1->setClickKey('key1');
        $button1->setPosition(0);
        $button1->setEnabled(true);

        $button2 = new MenuButton();
        $button2->setAccount($account);
        $button2->setName('Button 2');
        $button2->setType(MenuType::CLICK);
        $button2->setClickKey('key2');
        $button2->setPosition(1);
        $button2->setEnabled(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($button1);
        self::getEntityManager()->persist($button2);
        self::getEntityManager()->flush();

        $positions = [
            $button1->getId() => 10,
            $button2->getId() => 5,
        ];

        $this->getMenuService()->updateMenuPositions($positions);

        self::getEntityManager()->refresh($button1);
        self::getEntityManager()->refresh($button2);

        $this->assertSame(10, $button1->getPosition());
        $this->assertSame(5, $button2->getPosition());
    }

    public function testCopyMenuButton(): void
    {
        $account = new Account();
        $account->setName('Test Account');
        $account->setAppId('test_app_id');
        $account->setAppSecret('test_app_secret');

        $source = new MenuButton();
        $source->setAccount($account);
        $source->setName('Original Menu');
        $source->setType(MenuType::CLICK);
        $source->setClickKey('original_key');
        $source->setPosition(0);
        $source->setEnabled(true);

        $targetParent = new MenuButton();
        $targetParent->setAccount($account);
        $targetParent->setName('Target Parent');
        $targetParent->setType(MenuType::CLICK);
        $targetParent->setClickKey('parent_key');
        $targetParent->setPosition(0);
        $targetParent->setEnabled(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($source);
        self::getEntityManager()->persist($targetParent);
        self::getEntityManager()->flush();

        $result = $this->getMenuService()->copyMenuButton($source, $targetParent);

        $this->assertInstanceOf(MenuButton::class, $result);
        $this->assertStringContainsString('Original Menu', $result->getName() ?? '');
        $this->assertSame(MenuType::CLICK, $result->getType());
        $this->assertSame('original_key', $result->getClickKey());
        $this->assertSame($targetParent, $result->getParent());
    }

    public function testGetMenuTree(): void
    {
        $account = new Account();
        $account->setName('Test Account');
        $account->setAppId('test_app_id');
        $account->setAppSecret('test_app_secret');

        $rootButton = new MenuButton();
        $rootButton->setAccount($account);
        $rootButton->setName('Root Menu');
        $rootButton->setType(MenuType::CLICK);
        $rootButton->setClickKey('root_key');
        $rootButton->setPosition(0);
        $rootButton->setEnabled(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($rootButton);
        self::getEntityManager()->flush();

        $result = $this->getMenuService()->getMenuTree($account, true);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(MenuButton::class, $result[0]);
        $this->assertSame('Root Menu', $result[0]->getName());
    }

    public function testValidateMenuStructure(): void
    {
        $account = new Account();
        $account->setName('Test Account');
        $account->setAppId('test_app_id');
        $account->setAppSecret('test_app_secret');

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $result = $this->getMenuService()->validateMenuStructure($account);
        $this->assertIsArray($result);
    }

    public function testToggleMenuButton(): void
    {
        $account = new Account();
        $account->setName('Test Account');
        $account->setAppId('test_app_id');
        $account->setAppSecret('test_app_secret');

        $parentButton = new MenuButton();
        $parentButton->setAccount($account);
        $parentButton->setName('Parent Menu');
        $parentButton->setType(MenuType::CLICK);
        $parentButton->setClickKey('parent_key');
        $parentButton->setPosition(0);
        $parentButton->setEnabled(true);

        $childButton = new MenuButton();
        $childButton->setAccount($account);
        $childButton->setName('Child Menu');
        $childButton->setType(MenuType::CLICK);
        $childButton->setClickKey('child_key');
        $childButton->setPosition(0);
        $childButton->setEnabled(true);
        $childButton->setParent($parentButton);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($parentButton);
        self::getEntityManager()->persist($childButton);
        self::getEntityManager()->flush();

        $this->assertTrue($parentButton->isEnabled());
        $this->assertTrue($childButton->isEnabled());

        $this->getMenuService()->toggleMenuButton($parentButton, false);

        self::getEntityManager()->refresh($parentButton);
        self::getEntityManager()->refresh($childButton);

        $this->assertFalse($parentButton->isEnabled());
        // Child button status is independent - it should remain enabled
        $this->assertTrue($childButton->isEnabled(), 'Child button should remain enabled independently');
    }

    public function testMoveMenuButtonToDescendantThrowsException(): void
    {
        $account = new Account();
        $account->setName('Test Account');
        $account->setAppId('test_app_id');
        $account->setAppSecret('test_app_secret');

        $grandparent = new MenuButton();
        $grandparent->setAccount($account);
        $grandparent->setName('Grandparent');
        $grandparent->setType(MenuType::CLICK);
        $grandparent->setClickKey('gp_key');
        $grandparent->setPosition(0);
        $grandparent->setEnabled(true);

        $parent = new MenuButton();
        $parent->setAccount($account);
        $parent->setName('Parent');
        $parent->setType(MenuType::CLICK);
        $parent->setClickKey('parent_key');
        $parent->setPosition(0);
        $parent->setEnabled(true);
        $parent->setParent($grandparent);

        $child = new MenuButton();
        $child->setAccount($account);
        $child->setName('Child');
        $child->setType(MenuType::CLICK);
        $child->setClickKey('child_key');
        $child->setPosition(0);
        $child->setEnabled(true);
        $child->setParent($parent);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($grandparent);
        self::getEntityManager()->persist($parent);
        self::getEntityManager()->persist($child);
        self::getEntityManager()->flush();

        $this->expectException(MenuValidationException::class);
        $this->expectExceptionMessage('不能将菜单移动到其子节点下');

        $this->getMenuService()->moveMenuButton($parent, $child);
    }

    public function testMoveMenuButton(): void
    {
        $account = new Account();
        $account->setName('Test Account');
        $account->setAppId('test_app_id');
        $account->setAppSecret('test_app_secret');

        $menuButton = new MenuButton();
        $menuButton->setAccount($account);
        $menuButton->setName('Menu Button');
        $menuButton->setType(MenuType::CLICK);
        $menuButton->setClickKey('menu_key');
        $menuButton->setPosition(0);
        $menuButton->setEnabled(true);

        $newParent = new MenuButton();
        $newParent->setAccount($account);
        $newParent->setName('New Parent');
        $newParent->setType(MenuType::CLICK);
        $newParent->setClickKey('parent_key');
        $newParent->setPosition(0);
        $newParent->setEnabled(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuButton);
        self::getEntityManager()->persist($newParent);
        self::getEntityManager()->flush();

        $this->assertNull($menuButton->getParent());

        $this->getMenuService()->moveMenuButton($menuButton, $newParent);

        self::getEntityManager()->refresh($menuButton);
        $this->assertSame($newParent, $menuButton->getParent());
    }

    public function testGetMenuStructureForAccount(): void
    {
        $account = new Account();
        $account->setName('Test Account');
        $account->setAppId('test_app_id');
        $account->setAppSecret('test_app_secret');

        $menuButton = new MenuButton();
        $menuButton->setAccount($account);
        $menuButton->setName('Test Menu');
        $menuButton->setType(MenuType::CLICK);
        $menuButton->setClickKey('test_key');
        $menuButton->setPosition(0);
        $menuButton->setEnabled(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuButton);
        self::getEntityManager()->flush();

        $result = $this->getMenuService()->getMenuStructureForAccount($account);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('account', $result);
        $this->assertArrayHasKey('menus', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertSame(1, $result['total']);
    }

    public function testGetAllAccountsWithMenus(): void
    {
        // 确保数据库干净
        self::cleanDatabase();

        $account = new Account();
        $account->setName('Test Account');
        $account->setAppId('test_app_id_for_menu');
        $account->setAppSecret('test_app_secret');

        $menuButton = new MenuButton();
        $menuButton->setAccount($account);
        $menuButton->setName('Test Menu');
        $menuButton->setType(MenuType::CLICK);
        $menuButton->setClickKey('test_key');
        $menuButton->setPosition(0);
        $menuButton->setEnabled(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuButton);
        self::getEntityManager()->flush();

        $result = $this->getMenuService()->getAllAccountsWithMenus();

        $this->assertIsArray($result);
        $this->assertGreaterThan(0, $result); // 至少有一个账户
        $this->assertInstanceOf(Account::class, $result[0]);

        // 找到我们创建的账户
        $found = false;
        foreach ($result as $acc) {
            if ('Test Account' === $acc->getName()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, '创建的账户应该在结果中');
    }
}
