<?php

namespace WechatOfficialAccountMenuBundle\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Controller\UpdateMenuController;
use WechatOfficialAccountMenuBundle\Entity\MenuButton;
use WechatOfficialAccountMenuBundle\Enum\MenuType;

/**
 * @internal
 */
#[CoversClass(UpdateMenuController::class)]
#[RunTestsInSeparateProcesses]
final class UpdateMenuControllerTest extends AbstractWebTestCase
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

    private function createTestMenuButton(Account $account): MenuButton
    {
        $menuButton = new MenuButton();
        $menuButton->setAccount($account);
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
            $client->request('PUT', '/api/wechat/menu/update/test-menu-id');
            // 如果没有抛出异常，检查是否被重定向
            $response = $client->getResponse();
            $this->assertTrue($response->isRedirection(), 'Response should be redirect, got status: ' . $response->getStatusCode());
        } catch (AccessDeniedException $e) {
            // 如果抛出 AccessDeniedException，这是预期的行为
            $this->assertInstanceOf(AccessDeniedException::class, $e);
        }
    }

    public function testOnlyPutMethodIsAllowed(): void
    {
        $client = self::createClientWithDatabase();

        $account = $this->createTestAccount();
        $menuButton = $this->createTestMenuButton($account);

        // Test GET method - should return 405 Method Not Allowed
        try {
            $client->request('GET', '/api/wechat/menu/update/' . $menuButton->getId());
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertInstanceOf(MethodNotAllowedHttpException::class, $e);
        }

        // Test POST method - should return 405 Method Not Allowed
        try {
            $client->request('POST', '/api/wechat/menu/update/' . $menuButton->getId());
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertInstanceOf(MethodNotAllowedHttpException::class, $e);
        }

        // Test DELETE method - should return 405 Method Not Allowed
        try {
            $client->request('DELETE', '/api/wechat/menu/update/' . $menuButton->getId());
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertInstanceOf(MethodNotAllowedHttpException::class, $e);
        }

        // Test PATCH method - should return 405 Method Not Allowed
        try {
            $client->request('PATCH', '/api/wechat/menu/update/' . $menuButton->getId());
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertInstanceOf(MethodNotAllowedHttpException::class, $e);
        }
    }

    public function testUpdateNonExistentMenu(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->request('PUT', '/api/wechat/menu/update/999999', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'Updated Menu',
        ], JSON_THROW_ON_ERROR));

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testUpdateMenuWithEmptyData(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $account = $this->createTestAccount();
        $menuButton = $this->createTestMenuButton($account);

        $client->request('PUT', '/api/wechat/menu/update/' . $menuButton->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([], JSON_THROW_ON_ERROR));

        // 当提供空数据时，控制器使用现有值，所以应该返回成功
        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful(), 'Response should be successful, got status: ' . $response->getStatusCode());
    }

    public function testUpdateMenuWithInvalidData(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $account = $this->createTestAccount();
        $menuButton = $this->createTestMenuButton($account);

        $client->request('PUT', '/api/wechat/menu/update/' . $menuButton->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => '',
            'type' => 'view', // 使用有效的类型，但名称为空
        ], JSON_THROW_ON_ERROR));

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testUpdateMenuWithValidData(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $account = $this->createTestAccount();
        $menuButton = $this->createTestMenuButton($account);

        $client->request('PUT', '/api/wechat/menu/update/' . $menuButton->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'Updated Menu Name',
            'type' => 'click',
            'clickKey' => 'UPDATED_KEY',
            'enabled' => true,
        ], JSON_THROW_ON_ERROR));

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful(), 'Response should be successful, got status: ' . $response->getStatusCode());
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
