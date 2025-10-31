<?php

namespace WechatOfficialAccountMenuBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Controller\RollbackMenuVersionController;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;
use WechatOfficialAccountMenuBundle\Enum\MenuVersionStatus;

/**
 * @internal
 */
#[CoversClass(RollbackMenuVersionController::class)]
#[RunTestsInSeparateProcesses]
final class RollbackMenuVersionControllerTest extends AbstractWebTestCase
{
    public function testUnauthenticatedAccessDenied(): void
    {
        $client = self::createClientWithDatabase();

        $this->expectException(AccessDeniedException::class);
        $client->request('POST', '/api/wechat/menu-version/rollback/1');
    }

    public function testOnlyPostMethodIsAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $this->createNormalUser('test@example.com', 'password123');
        $this->loginAsUser($client, 'test@example.com', 'password123');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('GET', '/api/wechat/menu-version/rollback/1');
    }

    public function testRollbackNonExistentVersion(): void
    {
        $client = self::createClientWithDatabase();
        $this->createNormalUser('test@example.com', 'password123');
        $this->loginAsUser($client, 'test@example.com', 'password123');

        $client->request('POST', '/api/wechat/menu-version/rollback/999999');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertIsString($content);
        $data = json_decode($content, true);
        /** @var array{success: bool, error: string} $data */
        $this->assertFalse($data['success']);
        $this->assertEquals('版本不存在', $data['error']);
    }

    public function testRollbackValidVersion(): void
    {
        $client = self::createClientWithDatabase();
        $this->createNormalUser('test@example.com', 'password123');
        $this->loginAsUser($client, 'test@example.com', 'password123');

        // Create test version
        $version = $this->createTestMenuVersion();

        $client->request(
            'POST',
            '/api/wechat/menu-version/rollback/' . $version->getId()
        );

        // Rollback creates a new draft version based on the existing version
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertIsString($content);
        $data = json_decode($content, true);
        /** @var array{success: bool, message: string, data: array{status: string}} $data */
        $this->assertTrue($data['success']);
        $this->assertStringContainsString('回滚成功', $data['message']);
        $this->assertArrayHasKey('data', $data);
        $this->assertEquals('draft', $data['data']['status']);
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->createAdminUser('admin@example.com', 'admin123');
        $this->loginAsAdmin($client, 'admin@example.com', 'admin123');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/wechat/menu-version/rollback/1');
    }

    private function createTestAccount(): Account
    {
        $account = new Account();
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret');
        $account->setName('Test Account');

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        return $account;
    }

    private function createTestMenuVersion(): MenuVersion
    {
        $account = $this->createTestAccount();

        $version = new MenuVersion();
        $version->setAccount($account);
        $version->setVersion('1.0.0');
        $version->setStatus(MenuVersionStatus::PUBLISHED);
        $version->setDescription('Test Version');

        self::getEntityManager()->persist($version);
        self::getEntityManager()->flush();

        return $version;
    }
}
