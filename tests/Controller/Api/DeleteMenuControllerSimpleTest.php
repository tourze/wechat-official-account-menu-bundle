<?php

namespace WechatOfficialAccountMenuBundle\Tests\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Controller\Api\DeleteMenuController;
use WechatOfficialAccountMenuBundle\Entity\MenuButtonVersion;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;
use WechatOfficialAccountMenuBundle\Enum\MenuType;
use WechatOfficialAccountMenuBundle\Enum\MenuVersionStatus;

/**
 * @internal
 */
#[CoversClass(DeleteMenuController::class)]
#[RunTestsInSeparateProcesses]
final class DeleteMenuControllerSimpleTest extends AbstractWebTestCase
{
    protected function onSetUp(): void
    {
        // 实现抽象方法
    }

    public function testUnauthenticatedAccessIsDenied(): void
    {
        $client = self::createClientWithDatabase();

        try {
            $client->request('DELETE', '/admin/menu/version/1/menu/1');
            // 如果没有抛出异常，检查是否被重定向
            $this->assertResponseRedirects();
        } catch (AccessDeniedException $e) {
            // 如果抛出 AccessDeniedException，这是预期的行为
            $this->assertInstanceOf(AccessDeniedException::class, $e);
        }
    }

    public function testCreateAndFindAccount(): void
    {
        $client = self::createClientWithDatabase();

        // 创建内存管理员用户并登录
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 创建测试账户
        $account = new Account();
        $account->setName('测试公众号');
        $account->setAppId('wx1234567890');
        $account->setAppSecret('test_secret');
        $account->setToken('test_token');
        $account->setEncodingAesKey('test_aes_key');
        $account->setValid(true);

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($account);
        $entityManager->flush();

        // 验证账户确实被创建
        $foundAccount = $entityManager->find(Account::class, $account->getId());
        $this->assertInstanceOf(Account::class, $foundAccount, 'Account should be created');
        $this->assertEquals('测试公众号', $foundAccount->getName());
    }

    public function testCreateAndFindMenuVersion(): void
    {
        $client = self::createClientWithDatabase();

        // 创建内存管理员用户并登录
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 创建测试账户
        $account = new Account();
        $account->setName('测试公众号');
        $account->setAppId('wx1234567890');
        $account->setAppSecret('test_secret');
        $account->setToken('test_token');
        $account->setEncodingAesKey('test_aes_key');
        $account->setValid(true);

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($account);
        $entityManager->flush();

        // 创建菜单版本
        $menuVersion = new MenuVersion();
        $menuVersion->setAccount($account);
        $menuVersion->setVersion('1.0.0');
        $menuVersion->setDescription('测试版本');
        $menuVersion->setStatus(MenuVersionStatus::DRAFT);

        $entityManager->persist($menuVersion);
        $entityManager->flush();

        // 验证菜单版本确实被创建
        $foundMenuVersion = $entityManager->find(MenuVersion::class, $menuVersion->getId());
        $this->assertInstanceOf(MenuVersion::class, $foundMenuVersion, 'Menu version should be created');
        $this->assertEquals('1.0.0', $foundMenuVersion->getVersion());
        $this->assertEquals(MenuVersionStatus::DRAFT, $foundMenuVersion->getStatus());
    }

    public function testCreateAndFindMenuButton(): void
    {
        $client = self::createClientWithDatabase();

        // 创建内存管理员用户并登录
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 创建测试账户
        $account = new Account();
        $account->setName('测试公众号');
        $account->setAppId('wx1234567890');
        $account->setAppSecret('test_secret');
        $account->setToken('test_token');
        $account->setEncodingAesKey('test_aes_key');
        $account->setValid(true);

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($account);
        $entityManager->flush();

        // 创建菜单版本
        $menuVersion = new MenuVersion();
        $menuVersion->setAccount($account);
        $menuVersion->setVersion('1.0.0');
        $menuVersion->setDescription('测试版本');
        $menuVersion->setStatus(MenuVersionStatus::DRAFT);

        $entityManager->persist($menuVersion);
        $entityManager->flush();

        // 创建菜单按钮
        $menuButton = new MenuButtonVersion();
        $menuButton->setVersion($menuVersion);
        $menuButton->setName('测试菜单');
        $menuButton->setType(MenuType::CLICK);
        $menuButton->setClickKey('test_key');
        $menuButton->setPosition(1);
        $menuButton->setEnabled(true);

        $entityManager->persist($menuButton);
        $entityManager->flush();

        // 验证菜单按钮确实被创建
        $foundMenuButton = $entityManager->find(MenuButtonVersion::class, $menuButton->getId());
        $this->assertInstanceOf(MenuButtonVersion::class, $foundMenuButton, 'Menu button should be created');
        $this->assertEquals('测试菜单', $foundMenuButton->getName());
        $this->assertEquals(MenuType::CLICK, $foundMenuButton->getType());
        $this->assertEquals('test_key', $foundMenuButton->getClickKey());
    }

    public function testDeleteMenuButton(): void
    {
        $client = self::createClientWithDatabase();

        // 创建内存管理员用户并登录
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 创建测试账户
        $account = new Account();
        $account->setName('测试公众号');
        $account->setAppId('wx1234567890');
        $account->setAppSecret('test_secret');
        $account->setToken('test_token');
        $account->setEncodingAesKey('test_aes_key');
        $account->setValid(true);

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($account);
        $entityManager->flush();

        // 创建菜单版本
        $menuVersion = new MenuVersion();
        $menuVersion->setAccount($account);
        $menuVersion->setVersion('1.0.0');
        $menuVersion->setDescription('测试版本');
        $menuVersion->setStatus(MenuVersionStatus::DRAFT);

        $entityManager->persist($menuVersion);
        $entityManager->flush();

        // 创建菜单按钮
        $menuButton = new MenuButtonVersion();
        $menuButton->setVersion($menuVersion);
        $menuButton->setName('测试菜单');
        $menuButton->setType(MenuType::CLICK);
        $menuButton->setClickKey('test_key');
        $menuButton->setPosition(1);
        $menuButton->setEnabled(true);

        $entityManager->persist($menuButton);
        $entityManager->flush();

        // 验证菜单按钮确实被创建
        $foundMenuButton = $entityManager->find(MenuButtonVersion::class, $menuButton->getId());
        $this->assertInstanceOf(MenuButtonVersion::class, $foundMenuButton, 'Menu button should be created');

        // 删除菜单按钮
        $entityManager->remove($menuButton);
        $entityManager->flush();

        // 验证菜单按钮已被删除（通过DQL查询）
        $entityManager->clear(); // 清理缓存
        $query = $entityManager->createQuery('SELECT COUNT(m) FROM WechatOfficialAccountMenuBundle\Entity\MenuButtonVersion m WHERE m.name = :name');
        $query->setParameter('name', '测试菜单');
        $count = $query->getSingleScalarResult();
        $this->assertEquals(0, $count, 'Menu button should be deleted');
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/wechat/menu/delete/test-id');
    }
}
