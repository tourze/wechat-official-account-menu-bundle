<?php

namespace WechatOfficialAccountMenuBundle\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Controller\DeleteMenuController;
use WechatOfficialAccountMenuBundle\Entity\MenuButton;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;
use WechatOfficialAccountMenuBundle\Enum\MenuType;
use WechatOfficialAccountMenuBundle\Enum\MenuVersionStatus;

/**
 * @internal
 */
#[CoversClass(DeleteMenuController::class)]
#[RunTestsInSeparateProcesses]
final class DeleteMenuControllerTest extends AbstractWebTestCase
{
    private function createTestAccount(?Account $account = null): Account
    {
        if (null === $account) {
            $account = new Account();
            $account->setName('测试公众号');
            $account->setAppId('wx1234567890');
            $account->setAppSecret('test_secret');
            $account->setToken('test_token');
            $account->setEncodingAesKey('test_aes_key');
            $account->setValid(true);
        }

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($account);
        $entityManager->flush();

        return $account;
    }

    private function createTestMenuButton(Account $account): MenuButton
    {
        $menuButton = new MenuButton();
        $menuButton->setAccount($account);
        $menuButton->setName('测试菜单');
        $menuButton->setType(MenuType::CLICK);
        $menuButton->setClickKey('test_key');
        $menuButton->setPosition(1);
        $menuButton->setEnabled(true);

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($menuButton);
        $entityManager->flush();

        return $menuButton;
    }

    public function testUnauthenticatedAccessDenied(): void
    {
        $client = self::createClientWithDatabase();

        $this->expectException(AccessDeniedException::class);
        $client->request('DELETE', '/api/wechat/menu/delete/1');
    }

    public function testOnlyDeleteMethodIsAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $this->createNormalUser('test@example.com', 'password123');
        $this->loginAsUser($client, 'test@example.com', 'password123');

        $account = $this->createTestAccount();
        $menuButton = $this->createTestMenuButton($account);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('GET', '/api/wechat/menu/delete/' . $menuButton->getId());
    }

    public function testDeleteNonExistentMenu(): void
    {
        $client = self::createClientWithDatabase();
        $this->createAdminUser('admin@example.com', 'admin123');
        $this->loginAsAdmin($client, 'admin@example.com', 'admin123');

        $client->request('DELETE', '/api/wechat/menu/delete/999999');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testDeleteMenuWithChildren(): void
    {
        $client = self::createClientWithDatabase();
        $this->createAdminUser('admin@example.com', 'admin123');
        $this->loginAsAdmin($client, 'admin@example.com', 'admin123');

        $account = $this->createTestAccount();
        $parentMenu = $this->createTestMenuButton($account);
        $childMenu = $this->createTestMenuButton($account);
        $childMenu->setParent($parentMenu);

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($childMenu);
        $entityManager->flush();

        $client->request('DELETE', '/api/wechat/menu/delete/' . $parentMenu->getId());

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful(), 'Response should be successful, got status: ' . $response->getStatusCode());
    }

    public function testDeleteValidMenu(): void
    {
        $client = self::createClientWithDatabase();
        $this->createAdminUser('admin@example.com', 'admin123');
        $this->loginAsAdmin($client, 'admin@example.com', 'admin123');

        $account = $this->createTestAccount();
        $menuButton = $this->createTestMenuButton($account);

        $client->request('DELETE', '/api/wechat/menu/delete/' . $menuButton->getId());

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful(), 'Response should be successful, got status: ' . $response->getStatusCode());
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->createAdminUser('admin@example.com', 'admin123');
        $this->loginAsAdmin($client, 'admin@example.com', 'admin123');

        $account = $this->createTestAccount();
        $menuButton = $this->createTestMenuButton($account);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/wechat/menu/delete/' . $menuButton->getId());
    }
}
