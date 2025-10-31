<?php

namespace WechatOfficialAccountMenuBundle\Tests\Service;

use HttpClientBundle\Request\RequestInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tourze\DoctrineAsyncInsertBundle\Service\AsyncInsertService;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Service\OfficialAccountClient;
use WechatOfficialAccountMenuBundle\Exception\WechatApiException;
use WechatOfficialAccountMenuBundle\Service\WechatMenuApiService;

/**
 * @internal
 */
#[CoversClass(WechatMenuApiService::class)]
#[RunTestsInSeparateProcesses]
final class WechatMenuApiServiceTest extends AbstractIntegrationTestCase
{
    private WechatMenuApiService $wechatMenuApiService;

    private MockOfficialAccountClient $officialAccountClientMock;

    protected function onSetUp(): void
    {
        // 先获取服务再设置Mock
        // 这样可以避免容器服务已初始化的问题

        // 创建Mock对象
        $this->officialAccountClientMock = new MockOfficialAccountClient();

        // 在服务初始化之前设置Mock对象
        $container = self::getContainer();
        if (!$container->initialized(OfficialAccountClient::class)) {
            $container->set(OfficialAccountClient::class, $this->officialAccountClientMock);
        }
        $this->wechatMenuApiService = self::getService(WechatMenuApiService::class);
    }

    public function testCreateMenuSuccess(): void
    {
        $account = $this->createTestAccount();
        $menuData = ['button' => []];

        /** @phpstan-ignore method.resultUnused */
        $this->officialAccountClientMock
            ->expects($this->once())
            ->method('request')
        ;

        // 执行测试方法，不应该抛出异常
        $this->wechatMenuApiService->createMenu($account, $menuData);

        // 添加断言验证执行成功
        $this->assertTrue(true, '创建菜单成功，没有抛出异常');
    }

    public function testCreateMenuFailure(): void
    {
        $account = $this->createTestAccount();
        $menuData = ['button' => []];
        $exception = new \Exception('API Error');

        $this->officialAccountClientMock
            ->expects($this->once())
            ->method('request')
            ->willThrowException($exception)
        ;

        $this->expectException(WechatApiException::class);
        $this->expectExceptionMessage('创建菜单失败: API Error');

        $this->wechatMenuApiService->createMenu($account, $menuData);
    }

    public function testDeleteMenuSuccess(): void
    {
        $account = $this->createTestAccount();

        /** @phpstan-ignore method.resultUnused */
        $this->officialAccountClientMock
            ->expects($this->once())
            ->method('request')
        ;

        // 执行测试方法，不应该抛出异常
        $this->wechatMenuApiService->deleteMenu($account);

        // 添加断言验证执行成功
        $this->assertTrue(true, '删除菜单成功，没有抛出异常');
    }

    public function testDeleteMenuFailure(): void
    {
        $account = $this->createTestAccount();
        $exception = new \Exception('API Error');

        $this->officialAccountClientMock
            ->expects($this->once())
            ->method('request')
            ->willThrowException($exception)
        ;

        $this->expectException(WechatApiException::class);
        $this->expectExceptionMessage('删除菜单失败: API Error');

        $this->wechatMenuApiService->deleteMenu($account);
    }

    public function testCreateConditionalMenuSuccess(): void
    {
        $account = $this->createTestAccount();
        $menuData = ['button' => []];
        $matchRule = ['tag_id' => '101'];
        $expectedMenuId = 'menu123';

        $response = ['menuid' => $expectedMenuId];
        $this->officialAccountClientMock
            ->expects($this->once())
            ->method('request')
            ->willReturn($response)
        ;

        $result = $this->wechatMenuApiService->createConditionalMenu($account, $menuData, $matchRule);

        $this->assertSame($expectedMenuId, $result);
    }

    public function testCreateConditionalMenuMissingMenuId(): void
    {
        $account = $this->createTestAccount();
        $menuData = ['button' => []];
        $matchRule = ['tag_id' => '101'];

        $this->officialAccountClientMock
            ->expects($this->once())
            ->method('request')
            ->willReturn([])
        ;

        $this->expectException(WechatApiException::class);
        $this->expectExceptionMessage('响应中缺少menuid字段');

        $this->wechatMenuApiService->createConditionalMenu($account, $menuData, $matchRule);
    }

    public function testDeleteConditionalMenuSuccess(): void
    {
        $account = $this->createTestAccount();
        $menuId = 'menu123';

        /** @phpstan-ignore method.resultUnused */
        $this->officialAccountClientMock
            ->expects($this->once())
            ->method('request')
        ;

        // 执行测试方法，不应该抛出异常
        $this->wechatMenuApiService->deleteConditionalMenu($account, $menuId);

        // 添加断言验证执行成功
        $this->assertTrue(true, '删除个性化菜单成功，没有抛出异常');
    }

    public function testDeleteConditionalMenuFailure(): void
    {
        $account = $this->createTestAccount();
        $menuId = 'menu123';
        $exception = new \Exception('API Error');

        $this->officialAccountClientMock
            ->expects($this->once())
            ->method('request')
            ->willThrowException($exception)
        ;

        $this->expectException(WechatApiException::class);
        $this->expectExceptionMessage('删除个性化菜单失败: API Error');

        $this->wechatMenuApiService->deleteConditionalMenu($account, $menuId);
    }

    public function testTryMatchMenuSuccess(): void
    {
        $account = $this->createTestAccount();
        $userId = 'user123';
        $expectedResult = ['menu' => 'data'];

        $this->officialAccountClientMock
            ->expects($this->once())
            ->method('request')
            ->willReturn($expectedResult)
        ;

        $result = $this->wechatMenuApiService->tryMatchMenu($account, $userId);

        /** @var array<mixed> $result */
        $this->assertSame($expectedResult, $result);
    }

    public function testTryMatchMenuFailure(): void
    {
        $account = $this->createTestAccount();
        $userId = 'user123';
        $exception = new \Exception('API Error');

        $this->officialAccountClientMock
            ->expects($this->once())
            ->method('request')
            ->willThrowException($exception)
        ;

        $this->expectException(WechatApiException::class);
        $this->expectExceptionMessage('测试菜单匹配失败: API Error');

        $this->wechatMenuApiService->tryMatchMenu($account, $userId);
    }

    private function createTestAccount(): Account
    {
        $account = new Account();
        $account->setAppId('test_app_id');
        $account->setAppSecret('test_app_secret');
        $account->setName('Test Account');

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        return $account;
    }
}
