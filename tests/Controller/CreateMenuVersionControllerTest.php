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
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Controller\CreateMenuVersionController;

/**
 * @internal
 */
#[CoversClass(CreateMenuVersionController::class)]
#[RunTestsInSeparateProcesses]
final class CreateMenuVersionControllerTest extends AbstractWebTestCase
{
    public function testUnauthenticatedAccessDenied(): void
    {
        $client = self::createClientWithDatabase();

        $this->expectException(AccessDeniedException::class);
        $client->request('POST', '/api/wechat/menu-version/create/1');
    }

    public function testOnlyPostMethodIsAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $this->createNormalUser('test@example.com', 'password123');
        $this->loginAsUser($client, 'test@example.com', 'password123');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('GET', '/api/wechat/menu-version/create/1');
    }

    public function testCreateVersionWithNonExistentAccount(): void
    {
        $client = self::createClientWithDatabase();
        $this->createNormalUser('test@example.com', 'password123');
        $this->loginAsUser($client, 'test@example.com', 'password123');

        $data = [
            'description' => 'Test version',
        ];

        $this->expectException(NotFoundHttpException::class);
        $client->request(
            'POST',
            '/api/wechat/menu-version/create/999999',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data, JSON_THROW_ON_ERROR)
        );
    }

    public function testCreateVersionWithValidAccount(): void
    {
        $client = self::createClientWithDatabase();
        $this->createNormalUser('test@example.com', 'password123');
        $this->loginAsUser($client, 'test@example.com', 'password123');

        // Create a real account for testing
        $account = $this->createTestAccount();

        $data = [
            'description' => 'Test version description',
        ];

        $client->request(
            'POST',
            '/api/wechat/menu-version/create/' . $account->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data, JSON_THROW_ON_ERROR)
        );

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testCreateVersionWithEmptyData(): void
    {
        $client = self::createClientWithDatabase();
        $this->createNormalUser('test2@example.com', 'password123');
        $this->loginAsUser($client, 'test2@example.com', 'password123');

        // Create a real account for testing
        $account = $this->createTestAccount();

        $data = []; // Empty data is actually allowed - will create version with auto-generated values

        $client->request(
            'POST',
            '/api/wechat/menu-version/create/' . $account->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data, JSON_THROW_ON_ERROR)
        );

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->createAdminUser('admin@example.com', 'admin123');
        $this->loginAsAdmin($client, 'admin@example.com', 'admin123');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/wechat/menu-version/create/1');
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
}
