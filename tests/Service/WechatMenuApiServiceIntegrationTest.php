<?php

namespace WechatOfficialAccountMenuBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Exception\WechatApiException;
use WechatOfficialAccountMenuBundle\Service\WechatMenuApiService;

/**
 * @internal
 */
#[CoversClass(WechatMenuApiService::class)]
#[RunTestsInSeparateProcesses]
final class WechatMenuApiServiceIntegrationTest extends AbstractIntegrationTestCase
{
    private function getWechatMenuApiService(): WechatMenuApiService
    {
        return self::getService(WechatMenuApiService::class);
    }

    protected function onSetUp(): void
    {
        // 无需特殊设置
    }

    public function testServiceCanBeCreated(): void
    {
        $service = self::getService(WechatMenuApiService::class);
        $this->assertInstanceOf(WechatMenuApiService::class, $service);
    }

    public function testCreateMenu(): void
    {
        $account = new Account();
        $menuData = [
            'button' => [
                [
                    'type' => 'click',
                    'name' => 'Test Menu',
                    'key' => 'test_key',
                ],
            ],
        ];

        // 此方法会调用外部API，正常情况下会抛出异常（没有真实的微信配置）
        $this->expectException(WechatApiException::class);
        $this->getWechatMenuApiService()->createMenu($account, $menuData);
    }

    public function testCreateConditionalMenu(): void
    {
        $account = new Account();
        $menuData = [
            'button' => [
                [
                    'type' => 'click',
                    'name' => 'Test Menu',
                    'key' => 'test_key',
                ],
            ],
        ];
        $matchRule = [
            'tag_id' => '1',
        ];

        // 此方法会调用外部API，正常情况下会抛出异常
        $this->expectException(WechatApiException::class);
        $this->getWechatMenuApiService()->createConditionalMenu($account, $menuData, $matchRule);
    }

    public function testDeleteMenu(): void
    {
        $account = new Account();

        // 此方法会调用外部API，正常情况下会抛出异常
        $this->expectException(WechatApiException::class);
        $this->getWechatMenuApiService()->deleteMenu($account);
    }

    public function testDeleteConditionalMenu(): void
    {
        $account = new Account();
        $menuId = 'test_menu_id';

        // 此方法会调用外部API，正常情况下会抛出异常
        $this->expectException(WechatApiException::class);
        $this->getWechatMenuApiService()->deleteConditionalMenu($account, $menuId);
    }

    public function testTryMatchMenu(): void
    {
        $account = new Account();
        $userId = 'test_user_id';

        // 此方法会调用外部API，正常情况下会抛出异常
        $this->expectException(WechatApiException::class);
        $this->getWechatMenuApiService()->tryMatchMenu($account, $userId);
    }
}
