<?php

namespace WechatOfficialAccountMenuBundle\Tests\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Controller\Api\GetMenusController;
use WechatOfficialAccountMenuBundle\Entity\MenuButtonVersion;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;
use WechatOfficialAccountMenuBundle\Enum\MenuType;
use WechatOfficialAccountMenuBundle\Enum\MenuVersionStatus;

/**
 * @internal
 */
#[CoversClass(GetMenusController::class)]
#[RunTestsInSeparateProcesses]
final class GetMenusControllerIntegrationTest extends AbstractWebTestCase
{
    protected function onSetUp(): void
    {
        parent::onSetUp();
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

    public function testControllerExistsThroughHttpAccess(): void
    {
        $client = self::createClientWithDatabase();

        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);

        try {
            $client->request('GET', '/admin/menu/version/' . $menuVersion->getId() . '/menus');
            // 如果没有抛出异常，检查是否被重定向
            $response = $client->getResponse();
            $this->assertTrue($response->isRedirection(), 'Response should be redirect, got status: ' . $response->getStatusCode());
        } catch (AccessDeniedException $e) {
            // 如果抛出 AccessDeniedException，这是预期的行为
            $this->assertInstanceOf(AccessDeniedException::class, $e);
        }
    }

    public function testUnauthenticatedAccessIsRedirected(): void
    {
        $client = self::createClientWithDatabase();

        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);

        try {
            $client->request('GET', '/admin/menu/version/' . $menuVersion->getId() . '/menus');
            // 如果没有抛出异常，检查是否被重定向
            $response = $client->getResponse();
            $this->assertTrue($response->isRedirection(), 'Response should be redirect, got status: ' . $response->getStatusCode());
        } catch (AccessDeniedException $e) {
            // 如果抛出 AccessDeniedException，这是预期的行为
            $this->assertInstanceOf(AccessDeniedException::class, $e);
        }
    }

    public function testOnlyGetMethodIsAllowed(): void
    {
        $client = self::createClientWithDatabase();
        // 使用内存用户避免数据库依赖
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);

        $urlPath = '/admin/menu/version/' . $menuVersion->getId() . '/menus';

        // Test POST method - should return 405 Method Not Allowed
        try {
            $client->request('POST', $urlPath);
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertInstanceOf(MethodNotAllowedHttpException::class, $e);
        }

        // Test PUT method - should return 405 Method Not Allowed
        try {
            $client->request('PUT', $urlPath);
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertInstanceOf(MethodNotAllowedHttpException::class, $e);
        }

        // Test DELETE method - should return 405 Method Not Allowed
        try {
            $client->request('DELETE', $urlPath);
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertInstanceOf(MethodNotAllowedHttpException::class, $e);
        }
    }

    public function testAuthenticatedAccessWithValidVersion(): void
    {
        $client = self::createClientWithDatabase();
        // 使用内存用户避免数据库依赖
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);

        $client->request('GET', '/admin/menu/version/' . $menuVersion->getId() . '/menus');

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful(), 'Response should be successful, got status: ' . $response->getStatusCode());
    }

    public function testAuthenticatedAccessWithInvalidVersion(): void
    {
        $client = self::createClientWithDatabase();
        // 使用内存用户避免数据库依赖
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 创建一个账户和菜单版本以确保系统正常工作
        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);

        // 使用一个不存在的ID（当前ID+1000）来测试404响应
        $currentId = $menuVersion->getId();
        self::assertNotSame(null, $currentId, 'MenuVersion ID should not be null');
        $nonExistentId = (string) ((int) $currentId + 1000);

        try {
            $client->request('GET', '/admin/menu/version/' . $nonExistentId . '/menus');
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        } catch (NotFoundHttpException $e) {
            // 如果抛出 NotFoundHttpException，这也是预期的行为
            $this->assertInstanceOf(NotFoundHttpException::class, $e);
        }
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/admin/menu/version/test-version-id/menus');
    }
}
