<?php

declare(strict_types=1);

namespace WechatOfficialAccountMenuBundle\Tests\Controller\Test;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use WechatOfficialAccountMenuBundle\Controller\Test\LogoutController;

/**
 * @internal
 */
#[CoversClass(LogoutController::class)]
#[RunTestsInSeparateProcesses]
final class LogoutControllerTest extends AbstractWebTestCase
{
    public function testInvokeMethodReturnsTestResponse(): void
    {
        $controller = new LogoutController();
        $response = $controller->__invoke();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('Test logout', $response->getContent());
    }

    public function testControllerExistsAndIsInstantiable(): void
    {
        $controller = new LogoutController();
        $this->assertInstanceOf(LogoutController::class, $controller);
    }

    public function testMethodNotAllowed(string $method = 'PUT'): void
    {
        $client = self::createClientWithDatabase();

        // 测试不被允许的HTTP方法
        $client->request($method, '/logout');
        $this->assertResponseStatusCodeSame(405);
    }
}
