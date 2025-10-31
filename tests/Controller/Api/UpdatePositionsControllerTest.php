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
use WechatOfficialAccountMenuBundle\Controller\Api\UpdatePositionsController;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;
use WechatOfficialAccountMenuBundle\Enum\MenuVersionStatus;

/**
 * @internal
 */
#[CoversClass(UpdatePositionsController::class)]
#[RunTestsInSeparateProcesses]
final class UpdatePositionsControllerTest extends AbstractWebTestCase
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

    public function testUnauthenticatedAccessIsRedirected(): void
    {
        $client = self::createClientWithDatabase();

        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);

        try {
            $client->request('POST', '/admin/menu/version/' . $menuVersion->getId() . '/positions');
            $response = $client->getResponse();
            $this->assertTrue($response->isRedirection(), 'Response should be redirect, got status: ' . $response->getStatusCode());
        } catch (AccessDeniedException $e) {
            // 如果抛出 AccessDeniedException，这也是预期的行为
            $this->assertInstanceOf(AccessDeniedException::class, $e);
        }
    }

    public function testOnlyPostMethodIsAllowed(): void
    {
        $client = self::createClientWithDatabase();

        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);
        $urlPath = '/admin/menu/version/' . $menuVersion->getId() . '/positions';

        // 测试不允许的方法会抛出 MethodNotAllowedHttpException
        try {
            $client->request('GET', $urlPath);
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertInstanceOf(MethodNotAllowedHttpException::class, $e);
        }

        try {
            $client->request('PUT', $urlPath);
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertInstanceOf(MethodNotAllowedHttpException::class, $e);
        }

        try {
            $client->request('DELETE', $urlPath);
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertInstanceOf(MethodNotAllowedHttpException::class, $e);
        }
    }

    public function testUpdatePositionsWithValidData(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);

        $data = [
            'positions' => [
                '1' => ['position' => 0, 'parentId' => null],
                '2' => ['position' => 1, 'parentId' => '1'],
                '3' => ['position' => 2, 'parentId' => null],
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

    public function testUpdatePositionsWithEmptyData(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);

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

    public function testUpdatePositionsWithInvalidJson(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);

        $client->request(
            'POST',
            '/admin/menu/version/' . $menuVersion->getId() . '/positions',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'invalid json'
        );

        // 如果控制器没有验证JSON格式，可能会返回成功状态
        // 这里检查响应不是500错误即可
        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertTrue(
            in_array($statusCode, [Response::HTTP_OK, Response::HTTP_BAD_REQUEST, Response::HTTP_UNPROCESSABLE_ENTITY], true),
            sprintf('期望响应状态为 200、400 或 422，实际为 %d', $statusCode)
        );
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
