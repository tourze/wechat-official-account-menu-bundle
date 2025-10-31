<?php

namespace WechatOfficialAccountMenuBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use WechatOfficialAccountMenuBundle\Controller\GetMenuTreeController;

/**
 * @internal
 */
#[CoversClass(GetMenuTreeController::class)]
#[RunTestsInSeparateProcesses]
final class GetMenuTreeControllerTest extends AbstractWebTestCase
{
    public function testGetMenuTreeWithValidAccountId(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);
        $client->catchExceptions(true);

        $client->request('GET', '/api/wechat/menu/tree/valid-account-id');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGetMenuTreeWithInvalidAccountId(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);
        $client->catchExceptions(true);

        $client->request('GET', '/api/wechat/menu/tree/invalid-account-id');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGetMenuTreeWithOnlyEnabledParameter(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);
        $client->catchExceptions(true);

        $client->request('GET', '/api/wechat/menu/tree/test-account-id?onlyEnabled=true');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGetMenuTreeWithOnlyEnabledFalse(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);
        $client->catchExceptions(true);

        $client->request('GET', '/api/wechat/menu/tree/test-account-id?onlyEnabled=false');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testUnauthenticatedAccess(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/wechat/menu/tree/test-account-id');

        $response = $client->getResponse();
        $this->assertTrue($response->isClientError() || $response->isServerError());
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/wechat/menu/tree/test-account-id');
    }
}
