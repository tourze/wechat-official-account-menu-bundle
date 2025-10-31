<?php

namespace WechatOfficialAccountMenuBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use WechatOfficialAccountMenuBundle\Controller\CompareMenuVersionsController;

/**
 * @internal
 */
#[CoversClass(CompareMenuVersionsController::class)]
#[RunTestsInSeparateProcesses]
final class CompareMenuVersionsControllerTest extends AbstractWebTestCase
{
    public function testGetCompareMenuVersionsWithValidParameters(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->request('GET', '/api/wechat/menu-version/compare?version1=1&version2=2');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGetCompareMenuVersionsWithMissingParameters(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->request('GET', '/api/wechat/menu-version/compare');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testGetCompareMenuVersionsWithOnlyOneParameter(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->request('GET', '/api/wechat/menu-version/compare?version1=1');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/wechat/menu-version/compare');
    }

    public function testUnauthenticatedAccess(): void
    {
        $client = self::createClientWithDatabase();

        $client->catchExceptions(false);
        $this->expectException(AccessDeniedException::class);

        $client->request('GET', '/api/wechat/menu-version/compare?version1=1&version2=2');
    }
}
