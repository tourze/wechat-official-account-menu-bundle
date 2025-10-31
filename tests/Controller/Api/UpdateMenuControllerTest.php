<?php

namespace WechatOfficialAccountMenuBundle\Tests\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Controller\Api\UpdateMenuController;
use WechatOfficialAccountMenuBundle\Entity\MenuButtonVersion;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;
use WechatOfficialAccountMenuBundle\Enum\MenuType;
use WechatOfficialAccountMenuBundle\Enum\MenuVersionStatus;

/**
 * @internal
 */
#[CoversClass(UpdateMenuController::class)]
#[RunTestsInSeparateProcesses]
final class UpdateMenuControllerTest extends AbstractWebTestCase
{
    protected function onSetUp(): void
    {
        // 实现抽象方法
    }

    private function createTestAccount(?Account $account = null): Account
    {
        if (null === $account) {
            $account = new Account();
            $account->setName('测试公众号');
            $account->setAppId('wx1234567890');
            $account->setAppSecret('test_secret');
            $account->setToken('test_token');
            $account->setEncodingAesKey('test_aes_key');
            $account->setValid(true);
        }

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($account);
        $entityManager->flush();

        return $account;
    }

    private function createTestMenuVersion(Account $account): MenuVersion
    {
        $menuVersion = new MenuVersion();
        $menuVersion->setAccount($account);
        $menuVersion->setVersion('1.0.0');
        $menuVersion->setDescription('测试版本');
        $menuVersion->setStatus(MenuVersionStatus::DRAFT);

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($menuVersion);
        $entityManager->flush();

        return $menuVersion;
    }

    private function createTestMenuButton(MenuVersion $menuVersion): MenuButtonVersion
    {
        $menuButton = new MenuButtonVersion();
        $menuButton->setVersion($menuVersion);
        $menuButton->setName('测试菜单');
        $menuButton->setType(MenuType::CLICK);
        $menuButton->setClickKey('test_key');
        $menuButton->setPosition(1);
        $menuButton->setEnabled(true);

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($menuButton);
        $entityManager->flush();

        return $menuButton;
    }

    public function testUnauthenticatedAccessIsRedirected(): void
    {
        $client = self::createClientWithDatabase();

        try {
            $client->request('PUT', '/admin/menu/version/1/menu/1');
            // 如果没有抛出异常，检查是否被重定向
            $response = $client->getResponse();
            $this->assertTrue($response->isRedirection(), 'Response should be redirect, got status: ' . $response->getStatusCode());
        } catch (AccessDeniedException $e) {
            // 如果抛出 AccessDeniedException，这是预期的行为
            $this->assertInstanceOf(AccessDeniedException::class, $e);
        }
    }

    public function testUpdateMenuWithValidData(): void
    {
        $client = self::createClientWithDatabase();

        // 创建内存管理员用户并登录
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);
        $menuButton = $this->createTestMenuButton($menuVersion);

        $data = [
            'name' => '更新菜单',
            'type' => 'view',
            'url' => 'https://updated.com',
            'position' => 2,
            'enabled' => false,
        ];

        $client->request(
            'PUT',
            '/admin/menu/version/' . $menuVersion->getId() . '/menu/' . $menuButton->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data, JSON_THROW_ON_ERROR)
        );

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful(), 'Response should be successful, got status: ' . $response->getStatusCode());
    }

    public function testUpdateNonExistentMenu(): void
    {
        $client = self::createClientWithDatabase();

        // 创建内存管理员用户并登录
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 创建一个账户和菜单版本以确保系统正常工作
        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);

        $data = ['name' => '测试'];

        // 使用存在的版本ID但不存在的菜单ID来测试404响应
        $client->request(
            'PUT',
            '/admin/menu/version/' . $menuVersion->getId() . '/menu/999999',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data, JSON_THROW_ON_ERROR)
        );

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/wechat/menu/update/test-id');
    }
}
