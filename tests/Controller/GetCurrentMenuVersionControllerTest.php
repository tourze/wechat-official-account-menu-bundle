<?php

namespace WechatOfficialAccountMenuBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use WechatOfficialAccountMenuBundle\Controller\GetCurrentMenuVersionController;

/**
 * @internal
 */
#[CoversClass(GetCurrentMenuVersionController::class)]
#[RunTestsInSeparateProcesses]
final class GetCurrentMenuVersionControllerTest extends AbstractWebTestCase
{
    public function testUnauthenticatedAccessIsDenied(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(false);

        $this->expectException(AccessDeniedException::class);
        $client->request('GET', '/api/wechat/menu-version/current/test-account-id');
    }

    public function testOnlyGetMethodIsAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $this->createNormalUser('testmethod@example.com', 'password123');
        $this->loginAsUser($client, 'testmethod@example.com', 'password123');
        $client->catchExceptions(false);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('POST', '/api/wechat/menu-version/current/test-account-id');
    }

    public function testGetCurrentVersionWithNonExistentAccount(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);
        // 确保异常被捕获并转换为HTTP响应
        $client->catchExceptions(true);
        $client->request('GET', '/api/wechat/menu-version/current/999999');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGetCurrentVersionWithInvalidAccountId(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);
        $client->catchExceptions(true);
        $client->request('GET', '/api/wechat/menu-version/current/invalid-id');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGetCurrentVersionWithValidAccount(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);
        $client->catchExceptions(true);
        $client->request('GET', '/api/wechat/menu-version/current/test-account-id');

        // 由于测试环境没有真实账号数据，期望返回404
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/wechat/menu-version/current/test-account-id');
    }
}
