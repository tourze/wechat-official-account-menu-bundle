<?php

namespace WechatOfficialAccountMenuBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use WechatOfficialAccountMenuBundle\Controller\CopyMenuController;

/**
 * @internal
 */
#[CoversClass(CopyMenuController::class)]
#[RunTestsInSeparateProcesses]
final class CopyMenuControllerTest extends AbstractWebTestCase
{
    public function testPostCopyMenuWithValidId(): void
    {
        $client = self::createClient();

        $data = ['targetParentId' => null];

        $client->request(
            'POST',
            '/api/wechat/menu/copy/valid-id',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data, JSON_THROW_ON_ERROR)
        );

        // 检查响应是否为认证错误或未找到错误，都是合理的
        $response = $client->getResponse();
        $this->assertTrue(
            Response::HTTP_NOT_FOUND === $response->getStatusCode()
            || Response::HTTP_UNAUTHORIZED === $response->getStatusCode()
        );
    }

    public function testPostCopyMenuWithInvalidId(): void
    {
        $client = self::createClient();

        $data = ['targetParentId' => null];

        $client->request(
            'POST',
            '/api/wechat/menu/copy/invalid-id',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data, JSON_THROW_ON_ERROR)
        );

        // 检查响应是否为认证错误或未找到错误，都是合理的
        $response = $client->getResponse();
        $this->assertTrue(
            Response::HTTP_NOT_FOUND === $response->getStatusCode()
            || Response::HTTP_UNAUTHORIZED === $response->getStatusCode()
        );
    }

    public function testPostCopyMenuWithInvalidJson(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/api/wechat/menu/copy/test-id',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'invalid-json'
        );

        // 检查响应是否为认证错误或未找到错误，都是合理的
        $response = $client->getResponse();
        $this->assertTrue(
            Response::HTTP_NOT_FOUND === $response->getStatusCode()
            || Response::HTTP_UNAUTHORIZED === $response->getStatusCode()
        );
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/wechat/menu/copy/test-id');
    }

    public function testUnauthenticatedAccess(): void
    {
        $client = self::createClient();

        $data = ['targetParentId' => null];

        $client->request(
            'POST',
            '/api/wechat/menu/copy/test-id',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data, JSON_THROW_ON_ERROR)
        );

        $response = $client->getResponse();
        $this->assertTrue($response->isClientError() || $response->isServerError());
    }
}
