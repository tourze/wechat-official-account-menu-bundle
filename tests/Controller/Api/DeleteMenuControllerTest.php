<?php

namespace WechatOfficialAccountMenuBundle\Tests\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Controller\Api\DeleteMenuController;
use WechatOfficialAccountMenuBundle\Entity\MenuButtonVersion;
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
    private function createTestAccount(): Account
    {
        $account = new Account();
        $account->setName('测试公众号');
        $account->setAppId('wx1234567890');
        $account->setAppSecret('test_secret');
        $account->setToken('test_token');
        $account->setEncodingAesKey('test_aes_key');
        $account->setValid(true);

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($account);
        $entityManager->flush();

        return $account;
    }

    private function createTestMenuVersion(Account $account, MenuVersionStatus $status = MenuVersionStatus::DRAFT): MenuVersion
    {
        $menuVersion = new MenuVersion();
        $menuVersion->setAccount($account);
        $menuVersion->setVersion('1.0.0');
        $menuVersion->setDescription('测试版本');
        $menuVersion->setStatus($status);

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($menuVersion);
        $entityManager->flush();

        return $menuVersion;
    }

    private function createTestMenuButton(MenuVersion $menuVersion, ?MenuButtonVersion $parent = null): MenuButtonVersion
    {
        $menuButton = new MenuButtonVersion();
        $menuButton->setVersion($menuVersion);
        $menuButton->setName('测试菜单');
        $menuButton->setType(MenuType::CLICK);
        $menuButton->setClickKey('test_key');
        $menuButton->setPosition(1);
        $menuButton->setEnabled(true);

        if (null !== $parent) {
            $parent->addChild($menuButton);
        }

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($menuButton);
        $entityManager->flush();

        return $menuButton;
    }

    public function testUnauthenticatedAccessIsRedirected(): void
    {
        $client = self::createClientWithDatabase();

        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);
        $menuButton = $this->createTestMenuButton($menuVersion);

        try {
            $client->request('DELETE', "/admin/menu/version/{$menuVersion->getId()}/menu/{$menuButton->getId()}");
            $response = $client->getResponse();
            $this->assertTrue($response->isRedirection(), 'Response should be redirect, got status: ' . $response->getStatusCode());
        } catch (AccessDeniedException $e) {
            $this->assertInstanceOf(AccessDeniedException::class, $e);
        }
    }

    public function testDeleteMenuWithValidData(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);
        $menuButton = $this->createTestMenuButton($menuVersion);

        $client->request('DELETE', "/admin/menu/version/{$menuVersion->getId()}/menu/{$menuButton->getId()}");

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful(), 'Response should be successful, got status: ' . $response->getStatusCode());
        $this->assertJson(false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '{}');

        $responseData = json_decode(false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '{}', true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertSame('菜单删除成功', $responseData['message']);
    }

    public function testDeleteNonExistentMenu(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);

        $client->request('DELETE', "/admin/menu/version/{$menuVersion->getId()}/menu/999999");

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $responseData = json_decode(false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '{}', true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertSame('菜单不存在', $responseData['error']);
    }

    public function testDeleteMenuFromNonDraftVersion(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account, MenuVersionStatus::PUBLISHED);
        $menuButton = $this->createTestMenuButton($menuVersion);

        $client->request('DELETE', "/admin/menu/version/{$menuVersion->getId()}/menu/{$menuButton->getId()}");

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode(false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '{}', true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertSame('只能编辑草稿状态的版本', $responseData['error']);
    }

    public function testDeleteMenuWithChildren(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $account = $this->createTestAccount();
        $menuVersion = $this->createTestMenuVersion($account);
        $parentMenu = $this->createTestMenuButton($menuVersion);
        $this->createTestMenuButton($menuVersion, $parentMenu);

        $client->request('DELETE', "/admin/menu/version/{$menuVersion->getId()}/menu/{$parentMenu->getId()}");

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode(false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '{}', true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertSame('请先删除子菜单', $responseData['error']);
    }

    public function testDeleteMenuFromDifferentVersion(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $account = $this->createTestAccount();
        $menuVersion1 = $this->createTestMenuVersion($account);
        $menuVersion2 = $this->createTestMenuVersion($account);
        $menuButton = $this->createTestMenuButton($menuVersion2);

        $client->request('DELETE', "/admin/menu/version/{$menuVersion1->getId()}/menu/{$menuButton->getId()}");

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $responseData = json_decode(false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '{}', true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertSame('菜单不存在', $responseData['error']);
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/wechat/menu/delete/test-id');
    }
}
