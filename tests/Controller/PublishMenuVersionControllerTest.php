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
use WechatOfficialAccountMenuBundle\Controller\PublishMenuVersionController;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;
use WechatOfficialAccountMenuBundle\Enum\MenuVersionStatus;

/**
 * @internal
 */
#[CoversClass(PublishMenuVersionController::class)]
#[RunTestsInSeparateProcesses]
final class PublishMenuVersionControllerTest extends AbstractWebTestCase
{
    public function testUnauthenticatedAccessDenied(): void
    {
        $client = self::createClientWithDatabase();

        $this->expectException(AccessDeniedException::class);
        $client->request('POST', '/api/wechat/menu-version/publish/1');
    }

    public function testOnlyPostMethodIsAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $this->createNormalUser('test@example.com', 'password123');
        $this->loginAsUser($client, 'test@example.com', 'password123');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('GET', '/api/wechat/menu-version/publish/1');
    }

    public function testPublishNonExistentVersion(): void
    {
        $client = self::createClientWithDatabase();
        $this->createNormalUser('test@example.com', 'password123');
        $this->loginAsUser($client, 'test@example.com', 'password123');

        $client->request('POST', '/api/wechat/menu-version/publish/999999');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $response = $client->getResponse();
        $content = $response->getContent();
        $this->assertIsString($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertFalse($data['success']);
        $this->assertEquals('版本不存在', $data['error']);
    }

    public function testPublishValidVersion(): void
    {
        $client = self::createClientWithDatabase();
        $this->createNormalUser('test@example.com', 'password123');
        $this->loginAsUser($client, 'test@example.com', 'password123');

        // Create test version
        $version = $this->createTestMenuVersion();

        $client->request(
            'POST',
            '/api/wechat/menu-version/publish/' . $version->getId()
        );

        // Publishing may fail due to missing access_token in test environment
        // This is expected since we don't have real WeChat API access
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $response = $client->getResponse();
        $content = $response->getContent();
        $this->assertIsString($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertFalse($data['success']);
        $this->assertIsString($data['error']);
        $this->assertStringContainsString('access_token missing', $data['error']);
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->createAdminUser('admin@example.com', 'admin123');
        $this->loginAsAdmin($client, 'admin@example.com', 'admin123');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/wechat/menu-version/publish/1');
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
        $version->setStatus(MenuVersionStatus::DRAFT);
        $version->setDescription('Test Version');

        self::getEntityManager()->persist($version);
        self::getEntityManager()->flush();

        return $version;
    }
}
