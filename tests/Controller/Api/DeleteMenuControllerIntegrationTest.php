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
use WechatOfficialAccountMenuBundle\Controller\Api\DeleteMenuController;
use WechatOfficialAccountMenuBundle\Entity\MenuButtonVersion;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;
use WechatOfficialAccountMenuBundle\Enum\MenuType;
use WechatOfficialAccountMenuBundle\Enum\MenuVersionStatus;

/**
 * @internal
 *
 * */
#[CoversClass(DeleteMenuController::class)]
#[RunTestsInSeparateProcesses]
final class DeleteMenuControllerIntegrationTest extends AbstractWebTestCase
{
    protected function onSetUp(): void
    {
        // 实现抽象方法
    }

    private function createTestAccount(): Account
    {
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

    private function createTestMenuButton(MenuVersion $version): MenuButtonVersion
    {
        $menuButton = new MenuButtonVersion();
        $menuButton->setVersion($version);
        $menuButton->setType(MenuType::CLICK);
        $menuButton->setName('测试菜单');
        $menuButton->setClickKey('test_menu');
        $menuButton->setPosition(0);
        $menuButton->setEnabled(true);

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($menuButton);
        $entityManager->flush();

        return $menuButton;
    }

    public function testControllerExists(): void
    {
        $client = self::createClientWithDatabase();

        // 创建测试数据
        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);
        $menuButton = $this->createTestMenuButton($menuVersion);

        // 使用 InMemoryUser 进行认证
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('DELETE', '/admin/menu/version/' . $menuVersion->getId() . '/menu/' . $menuButton->getId());
        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful(), 'Response should be successful, got status: ' . $response->getStatusCode());
    }

    public function testUnauthenticatedAccess(): void
    {
        $client = self::createClientWithDatabase();

        // 创建测试数据
        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);
        $menuButton = $this->createTestMenuButton($menuVersion);

        try {
            $client->request('DELETE', '/admin/menu/version/' . $menuVersion->getId() . '/menu/' . $menuButton->getId());
            // 如果没有抛出异常，检查是否被重定向
            $response = $client->getResponse();
            $this->assertTrue($response->isRedirection(), 'Response should be redirect, got status: ' . $response->getStatusCode());
        } catch (AccessDeniedException $e) {
            // 如果抛出 AccessDeniedException，这是预期的行为
            $this->assertInstanceOf(AccessDeniedException::class, $e);
        }
    }

    public function testHttpMethodsHandling(): void
    {
        $client = self::createClientWithDatabase();

        // 创建测试数据
        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);
        $menuButton = $this->createTestMenuButton($menuVersion);

        $path = '/admin/menu/version/' . $menuVersion->getId() . '/menu/' . $menuButton->getId();

        // Test GET method - 应该返回Method Not Allowed
        try {
            $client->request('GET', $path);
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertInstanceOf(MethodNotAllowedHttpException::class, $e);
        }

        // Test POST method - 应该返回Method Not Allowed
        try {
            $client->request('POST', $path);
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertInstanceOf(MethodNotAllowedHttpException::class, $e);
        }

        // Test PUT method - 可能会被路由接受，检查访问权限
        try {
            $client->request('PUT', $path);
            $response = $client->getResponse();
            $this->assertTrue($response->isRedirection(), 'Response should be redirect, got status: ' . $response->getStatusCode());
        } catch (AccessDeniedException $e) {
            $this->assertInstanceOf(AccessDeniedException::class, $e);
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertInstanceOf(MethodNotAllowedHttpException::class, $e);
        }

        // Test DELETE method (unauthenticated) - 检查访问权限
        try {
            $client->request('DELETE', $path);
            $response = $client->getResponse();
            $this->assertTrue($response->isRedirection(), 'Response should be redirect, got status: ' . $response->getStatusCode());
        } catch (AccessDeniedException $e) {
            $this->assertInstanceOf(AccessDeniedException::class, $e);
        }

        // Test PATCH method - 应该返回Method Not Allowed
        try {
            $client->request('PATCH', $path);
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertInstanceOf(MethodNotAllowedHttpException::class, $e);
        }

        // Test HEAD method - 应该返回Method Not Allowed
        try {
            $client->request('HEAD', $path);
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertInstanceOf(MethodNotAllowedHttpException::class, $e);
        }

        // Test OPTIONS method - 应该返回Method Not Allowed
        try {
            $client->request('OPTIONS', $path);
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertInstanceOf(MethodNotAllowedHttpException::class, $e);
        }
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
