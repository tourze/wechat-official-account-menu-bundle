<?php

namespace WechatOfficialAccountMenuBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use WechatOfficialAccountMenuBundle\Controller\GetMenuVersionController;

/**
 * @internal
 */
#[CoversClass(GetMenuVersionController::class)]
#[RunTestsInSeparateProcesses]
final class GetMenuVersionControllerTest extends AbstractWebTestCase
{
    public function testUnauthenticatedAccessIsRedirected(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);
        $client->catchExceptions(true);
        $client->request('GET', '/api/wechat/menu-version/test-version-id');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testOnlyGetMethodIsAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $this->createNormalUser('testmethod@example.com', 'password123');
        $this->loginAsUser($client, 'testmethod@example.com', 'password123');
        $client->catchExceptions(true);

        $client->request('POST', '/api/wechat/menu-version/test-version-id');
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());

        $client->request('PUT', '/api/wechat/menu-version/test-version-id');
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());

        $client->request('DELETE', '/api/wechat/menu-version/test-version-id');
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());

        $client->request('PATCH', '/api/wechat/menu-version/test-version-id');
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
    }

    public function testGetNonExistentMenuVersion(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);
        $client->catchExceptions(true);
        $client->request('GET', '/api/wechat/menu-version/999999');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGetMenuVersionWithInvalidId(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);
        $client->catchExceptions(true);
        $client->request('GET', '/api/wechat/menu-version/invalid-id');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGetValidMenuVersion(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);
        $client->catchExceptions(true);
        $client->request('GET', '/api/wechat/menu-version/test-version-id');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/wechat/menu-version/test-version-id');
    }
}
