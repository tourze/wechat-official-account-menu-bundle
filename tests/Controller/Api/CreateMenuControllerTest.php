<?php

namespace WechatOfficialAccountMenuBundle\Tests\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Controller\Api\CreateMenuController;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;
use WechatOfficialAccountMenuBundle\Enum\MenuVersionStatus;

/**
 * @internal
 */
#[CoversClass(CreateMenuController::class)]
#[RunTestsInSeparateProcesses]
final class CreateMenuControllerTest extends AbstractWebTestCase
{
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

    public function testUnauthenticatedAccessIsRedirected(): void
    {
        $client = self::createClientWithDatabase();

        try {
            $client->request('POST', '/admin/menu/version/1/menu');
            // 如果没有抛出异常，检查是否被重定向
            $response = $client->getResponse();
            $this->assertTrue($response->isRedirection(), 'Response should be redirect, got status: ' . $response->getStatusCode());
        } catch (AccessDeniedException $e) {
            // 如果抛出 AccessDeniedException，这是预期的行为
            $this->assertInstanceOf(AccessDeniedException::class, $e);
        }
    }

    public function testCreateMenuWithValidData(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);

        $data = [
            'name' => '测试菜单',
            'type' => 'click',
            'clickKey' => 'test_key',
            'position' => 1,
            'enabled' => true,
        ];

        $client->request(
            'POST',
            '/admin/menu/version/' . $menuVersion->getId() . '/menu',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data, JSON_THROW_ON_ERROR)
        );

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful(), 'Response should be successful, got status: ' . $response->getStatusCode());
    }

    public function testCreateMenuWithMissingRequiredFields(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);

        $data = [
            'type' => 'click',
        ];

        $client->request(
            'POST',
            '/admin/menu/version/' . $menuVersion->getId() . '/menu',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data, JSON_THROW_ON_ERROR)
        );

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testCreateMenuWithInvalidJsonData(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);

        $client->request(
            'POST',
            '/admin/menu/version/' . $menuVersion->getId() . '/menu',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'invalid json'
        );

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testHttpMethodsHandling(): void
    {
        $client = self::createClientWithDatabase();

        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);

        // Test GET method - should return 405 Method Not Allowed
        try {
            $client->request('GET', '/admin/menu/version/' . $menuVersion->getId() . '/menu');
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertInstanceOf(MethodNotAllowedHttpException::class, $e);
        }

        // Test PUT method - should return 405 Method Not Allowed
        try {
            $client->request('PUT', '/admin/menu/version/' . $menuVersion->getId() . '/menu');
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertInstanceOf(MethodNotAllowedHttpException::class, $e);
        }

        // Test DELETE method - should return 405 Method Not Allowed
        try {
            $client->request('DELETE', '/admin/menu/version/' . $menuVersion->getId() . '/menu');
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertInstanceOf(MethodNotAllowedHttpException::class, $e);
        }

        // Test PATCH method - should return 405 Method Not Allowed
        try {
            $client->request('PATCH', '/admin/menu/version/' . $menuVersion->getId() . '/menu');
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertInstanceOf(MethodNotAllowedHttpException::class, $e);
        }

        // Test HEAD method - should return 405 Method Not Allowed
        try {
            $client->request('HEAD', '/admin/menu/version/' . $menuVersion->getId() . '/menu');
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertInstanceOf(MethodNotAllowedHttpException::class, $e);
        }

        // Test OPTIONS method - should return 405 Method Not Allowed
        try {
            $client->request('OPTIONS', '/admin/menu/version/' . $menuVersion->getId() . '/menu');
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertInstanceOf(MethodNotAllowedHttpException::class, $e);
        }
    }

    public function testCreateMenuWithParentId(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);

        $data = [
            'name' => '子菜单',
            'type' => 'view',
            'url' => 'https://example.com',
            'parentId' => 1,
            'position' => 1,
            'enabled' => true,
        ];

        $client->request(
            'POST',
            '/admin/menu/version/' . $menuVersion->getId() . '/menu',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data, JSON_THROW_ON_ERROR)
        );

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful(), 'Response should be successful, got status: ' . $response->getStatusCode());
    }

    public function testCreateMenuWithMiniProgramData(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);

        $data = [
            'name' => '小程序菜单',
            'type' => 'miniprogram',
            'appId' => 'wx1234567890',
            'pagePath' => 'pages/index/index',
            'position' => 1,
            'enabled' => true,
        ];

        $client->request(
            'POST',
            '/admin/menu/version/' . $menuVersion->getId() . '/menu',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data, JSON_THROW_ON_ERROR)
        );

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful(), 'Response should be successful, got status: ' . $response->getStatusCode());
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->createAdminUser('admin@example.com', 'admin123');
        $this->loginAsAdmin($client, 'admin@example.com', 'admin123');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/wechat/menu/create/test-id');
    }
}
