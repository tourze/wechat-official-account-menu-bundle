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
use WechatOfficialAccountMenuBundle\Controller\ToggleMenuController;
use WechatOfficialAccountMenuBundle\Entity\MenuButton;
use WechatOfficialAccountMenuBundle\Enum\MenuType;

/**
 * @internal
 */
#[CoversClass(ToggleMenuController::class)]
#[RunTestsInSeparateProcesses]
final class ToggleMenuControllerTest extends AbstractWebTestCase
{
    public function testUnauthenticatedAccessDenied(): void
    {
        $client = self::createClientWithDatabase();

        $this->expectException(AccessDeniedException::class);
        $client->request('PATCH', '/api/wechat/menu/toggle/1');
    }

    public function testOnlyPatchMethodIsAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $this->createNormalUser('test@example.com', 'password123');
        $this->loginAsUser($client, 'test@example.com', 'password123');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('GET', '/api/wechat/menu/toggle/1');
    }

    public function testToggleNonExistentMenu(): void
    {
        $client = self::createClientWithDatabase();
        $this->createNormalUser('test@example.com', 'password123');
        $this->loginAsUser($client, 'test@example.com', 'password123');

        $data = [
            'enabled' => true,
        ];

        $client->request(
            'PATCH',
            '/api/wechat/menu/toggle/999999',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data, JSON_THROW_ON_ERROR)
        );

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testToggleMenuWithValidData(): void
    {
        $client = self::createClientWithDatabase();
        $this->createNormalUser('test@example.com', 'password123');
        $this->loginAsUser($client, 'test@example.com', 'password123');

        // Create test menu button
        $menuButton = $this->createTestMenuButton();

        $data = [
            'enabled' => false,
        ];

        $client->request(
            'PATCH',
            '/api/wechat/menu/toggle/' . $menuButton->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data, JSON_THROW_ON_ERROR)
        );

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testToggleMenuWithEmptyData(): void
    {
        $client = self::createClientWithDatabase();
        $this->createNormalUser('test2@example.com', 'password123');
        $this->loginAsUser($client, 'test2@example.com', 'password123');

        // Create test menu button
        $menuButton = $this->createTestMenuButton();

        $data = []; // Empty data should toggle the current state

        $client->request(
            'PATCH',
            '/api/wechat/menu/toggle/' . $menuButton->getId(),
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
        $client->request($method, '/api/wechat/menu/toggle/1');
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

    private function createTestMenuButton(): MenuButton
    {
        $account = $this->createTestAccount();

        $menuButton = new MenuButton();
        $menuButton->setAccount($account);
        $menuButton->setName('Test Menu');
        $menuButton->setType(MenuType::CLICK);
        $menuButton->setClickKey('test_key');
        $menuButton->setPosition(1);
        $menuButton->setEnabled(true);

        self::getEntityManager()->persist($menuButton);
        self::getEntityManager()->flush();

        return $menuButton;
    }
}
