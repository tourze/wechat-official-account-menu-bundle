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
use WechatOfficialAccountMenuBundle\Controller\Api\UpdatePositionsController;
use WechatOfficialAccountMenuBundle\Entity\MenuButtonVersion;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;
use WechatOfficialAccountMenuBundle\Enum\MenuType;
use WechatOfficialAccountMenuBundle\Enum\MenuVersionStatus;

/**
 * @internal
 */
#[CoversClass(UpdatePositionsController::class)]
#[RunTestsInSeparateProcesses]
final class UpdatePositionsControllerIntegrationTest extends AbstractWebTestCase
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

    /**
     * @return array<MenuButtonVersion>
     */
    private function createTestMenuButtons(MenuVersion $version): array
    {
        $menuButton1 = new MenuButtonVersion();
        $menuButton1->setVersion($version);
        $menuButton1->setType(MenuType::CLICK);
        $menuButton1->setName('菜单1');
        $menuButton1->setClickKey('menu_1');
        $menuButton1->setPosition(0);
        $menuButton1->setEnabled(true);

        $menuButton2 = new MenuButtonVersion();
        $menuButton2->setVersion($version);
        $menuButton2->setType(MenuType::VIEW);
        $menuButton2->setName('菜单2');
        $menuButton2->setUrl('https://example.com');
        $menuButton2->setPosition(1);
        $menuButton2->setEnabled(true);
        $menuButton2->setParent($menuButton1);

        $menuButton3 = new MenuButtonVersion();
        $menuButton3->setVersion($version);
        $menuButton3->setType(MenuType::CLICK);
        $menuButton3->setName('菜单3');
        $menuButton3->setClickKey('menu_3');
        $menuButton3->setPosition(2);
        $menuButton3->setEnabled(true);

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($menuButton1);
        $entityManager->persist($menuButton2);
        $entityManager->persist($menuButton3);
        $entityManager->flush();

        return [$menuButton1, $menuButton2, $menuButton3];
    }

    public function testControllerExistsThroughHttpAccess(): void
    {
        $client = self::createClientWithDatabase();

        // 创建测试数据
        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);
        $this->createTestMenuButtons($menuVersion);

        try {
            $client->request('POST', '/admin/menu/version/' . $menuVersion->getId() . '/positions');
            $response = $client->getResponse();
            $this->assertTrue($response->isRedirection(), 'Response should be redirect, got status: ' . $response->getStatusCode());
        } catch (AccessDeniedException $e) {
            $this->assertInstanceOf(AccessDeniedException::class, $e);
        }
    }

    public function testUnauthenticatedAccessIsRedirected(): void
    {
        $client = self::createClientWithDatabase();

        // 创建测试数据
        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);
        $this->createTestMenuButtons($menuVersion);

        try {
            $client->request('POST', '/admin/menu/version/' . $menuVersion->getId() . '/positions');
            $response = $client->getResponse();
            $this->assertTrue($response->isRedirection(), 'Response should be redirect, got status: ' . $response->getStatusCode());
        } catch (AccessDeniedException $e) {
            $this->assertInstanceOf(AccessDeniedException::class, $e);
        }
    }

    public function testOnlyPostMethodIsAllowed(): void
    {
        $client = self::createClientWithDatabase();

        // 创建测试数据
        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);
        $this->createTestMenuButtons($menuVersion);

        $path = '/admin/menu/version/' . $menuVersion->getId() . '/positions';

        // Test GET method - 应该返回Method Not Allowed
        try {
            $client->request('GET', $path);
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertInstanceOf(MethodNotAllowedHttpException::class, $e);
        }

        // Test PUT method - 应该返回Method Not Allowed
        try {
            $client->request('PUT', $path);
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertInstanceOf(MethodNotAllowedHttpException::class, $e);
        }

        // Test DELETE method - 应该返回Method Not Allowed
        try {
            $client->request('DELETE', $path);
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertInstanceOf(MethodNotAllowedHttpException::class, $e);
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

    public function testAuthenticatedUpdateWithValidData(): void
    {
        $client = self::createClientWithDatabase();

        // 创建测试数据
        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);
        $menuButtons = $this->createTestMenuButtons($menuVersion);

        // 使用 InMemoryUser 进行认证
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $data = [
            'positions' => [
                (string) $menuButtons[0]->getId() => ['position' => 0, 'parentId' => null],
                (string) $menuButtons[1]->getId() => ['position' => 1, 'parentId' => (string) $menuButtons[0]->getId()],
                (string) $menuButtons[2]->getId() => ['position' => 2, 'parentId' => null],
            ],
        ];

        $client->request(
            'POST',
            '/admin/menu/version/' . $menuVersion->getId() . '/positions',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data, JSON_THROW_ON_ERROR)
        );

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful(), 'Response should be successful, got status: ' . $response->getStatusCode());
    }

    public function testAuthenticatedUpdateWithEmptyData(): void
    {
        $client = self::createClientWithDatabase();

        // 创建测试数据
        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);
        $this->createTestMenuButtons($menuVersion);

        // 使用 InMemoryUser 进行认证
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $data = ['positions' => []];

        $client->request(
            'POST',
            '/admin/menu/version/' . $menuVersion->getId() . '/positions',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data, JSON_THROW_ON_ERROR)
        );

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful(), 'Response should be successful, got status: ' . $response->getStatusCode());
    }

    public function testAuthenticatedUpdateWithInvalidJson(): void
    {
        $client = self::createClientWithDatabase();

        // 创建测试数据
        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);
        $this->createTestMenuButtons($menuVersion);

        // 使用 InMemoryUser 进行认证
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request(
            'POST',
            '/admin/menu/version/' . $menuVersion->getId() . '/positions',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'invalid json'
        );

        // 控制器现在正确地验证JSON输入，对无效JSON返回400错误
        $response = $client->getResponse();
        $this->assertEquals(400, $response->getStatusCode(), 'Response should return 400 for invalid JSON, got status: ' . $response->getStatusCode());
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/admin/menu/version/test-version-id/positions');
    }
}
