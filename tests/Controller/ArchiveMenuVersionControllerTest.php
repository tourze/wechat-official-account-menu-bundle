<?php

namespace WechatOfficialAccountMenuBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use WechatOfficialAccountMenuBundle\Controller\ArchiveMenuVersionController;

/**
 * @internal
 */
#[CoversClass(ArchiveMenuVersionController::class)]
#[RunTestsInSeparateProcesses]
final class ArchiveMenuVersionControllerTest extends AbstractWebTestCase
{
    public function testDeleteArchiveMenuVersionWithValidId(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->request('DELETE', '/api/wechat/menu-version/archive/valid-id');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testDeleteArchiveMenuVersionWithInvalidId(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->request('DELETE', '/api/wechat/menu-version/archive/invalid-id');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/wechat/menu-version/archive/test-id');
    }
}
