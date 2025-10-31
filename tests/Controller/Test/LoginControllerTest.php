<?php

declare(strict_types=1);

namespace WechatOfficialAccountMenuBundle\Tests\Controller\Test;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use WechatOfficialAccountMenuBundle\Controller\Test\LoginController;

/**
 * @internal
 */
#[CoversClass(LoginController::class)]
#[RunTestsInSeparateProcesses]
final class LoginControllerTest extends AbstractWebTestCase
{
    public function testInvokeMethodReturnsTestResponse(): void
    {
        $controller = new LoginController();
        $response = $controller->__invoke();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('Test login page', $response->getContent());
    }

    public function testControllerExistsAndIsInstantiable(): void
    {
        $controller = new LoginController();
        $this->assertInstanceOf(LoginController::class, $controller);
    }

    public function testMethodNotAllowed(string $method = 'PUT'): void
    {
        $client = self::createClientWithDatabase();

        // 测试不被允许的HTTP方法
        $client->request($method, '/login');
        $this->assertResponseStatusCodeSame(405);
    }
}
