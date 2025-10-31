<?php

namespace WechatOfficialAccountMenuBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use WechatOfficialAccountMenuBundle\Controller\PreviewMenuVersionController;

/**
 * @internal
 */
#[CoversClass(PreviewMenuVersionController::class)]
#[RunTestsInSeparateProcesses]
final class PreviewMenuVersionControllerTest extends AbstractWebTestCase
{
    public function testGETGetRequest(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);
        $client->catchExceptions(true);
        $client->request('GET', '/api/wechat/menu-version/preview/test-id');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/wechat/menu-version/preview/test-id');
    }
}
