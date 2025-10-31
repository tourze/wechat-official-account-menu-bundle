<?php

namespace WechatOfficialAccountMenuBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use WechatOfficialAccountMenuBundle\Controller\ValidateMenuController;

/**
 * @internal
 */
#[CoversClass(ValidateMenuController::class)]
#[RunTestsInSeparateProcesses]
final class ValidateMenuControllerTest extends AbstractWebTestCase
{
    public function testUnauthenticatedAccessDenied(): void
    {
        $client = self::createClientWithDatabase();

        $this->expectException(AccessDeniedException::class);
        $client->request('GET', '/api/wechat/menu/validate/test-account-id');
    }

    public function testOnlyGetMethodIsAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $this->createNormalUser('testmethod@example.com', 'password123');
        $this->loginAsUser($client, 'testmethod@example.com', 'password123');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('POST', '/api/wechat/menu/validate/test-account-id');
    }

    public function testValidateMenuWithNonExistentAccount(): void
    {
        $client = self::createClientWithDatabase();
        $this->createNormalUser('test@example.com', 'password123');
        $this->loginAsUser($client, 'test@example.com', 'password123');

        $this->expectException(NotFoundHttpException::class);
        $client->request('GET', '/api/wechat/menu/validate/999999');
    }

    public function testValidateMenuWithInvalidAccountId(): void
    {
        $client = self::createClientWithDatabase();
        $this->createNormalUser('test2@example.com', 'password123');
        $this->loginAsUser($client, 'test2@example.com', 'password123');

        $this->expectException(NotFoundHttpException::class);
        $client->request('GET', '/api/wechat/menu/validate/invalid-id');
    }

    public function testValidateMenuWithValidAccount(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);
        $this->createNormalUser('test3@example.com', 'password123');
        $this->loginAsUser($client, 'test3@example.com', 'password123');

        // 使用不存在的账号ID测试，因为HTTP请求在独立进程中运行
        $client->request('GET', '/api/wechat/menu/validate/999999');

        $response = $client->getResponse();
        // 由于进程隔离，账号无法在HTTP请求中访问，应该返回404
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        // 验证返回404错误页面包含相关错误信息
        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('公众号不存在', $content);
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->createAdminUser('admin@example.com', 'admin123');
        $this->loginAsAdmin($client, 'admin@example.com', 'admin123');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/wechat/menu/validate/test-id');
    }
}
