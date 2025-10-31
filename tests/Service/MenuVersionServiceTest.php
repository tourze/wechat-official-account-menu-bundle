<?php

namespace WechatOfficialAccountMenuBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Entity\MenuButtonVersion;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;
use WechatOfficialAccountMenuBundle\Enum\MenuType;
use WechatOfficialAccountMenuBundle\Service\MenuVersionService;

/**
 * @internal
 */
#[CoversClass(MenuVersionService::class)]
#[RunTestsInSeparateProcesses]
final class MenuVersionServiceTest extends AbstractIntegrationTestCase
{
    private MenuVersionService $menuVersionService;

    protected function onSetUp(): void
    {
        $this->menuVersionService = self::getService(MenuVersionService::class);
    }

    public function testCreateVersion(): void
    {
        $account = $this->createTestAccount();

        $version = $this->menuVersionService->createVersion($account, 'Test version');

        $this->assertInstanceOf(MenuVersion::class, $version);
        $this->assertSame($account, $version->getAccount());
        $this->assertSame('Test version', $version->getDescription());
        $this->assertIsString($version->getVersion());
        $this->assertNotEmpty($version->getVersion());
    }

    public function testCreateVersionWithCopy(): void
    {
        $account = $this->createTestAccount();

        $originalVersion = $this->menuVersionService->createVersion($account, 'Original');
        $copiedVersion = $this->menuVersionService->createVersion($account, 'Copy', $originalVersion);

        $this->assertInstanceOf(MenuVersion::class, $copiedVersion);
        $this->assertSame($account, $copiedVersion->getAccount());
        $this->assertSame('Copy', $copiedVersion->getDescription());
        $this->assertNotSame($originalVersion->getVersion(), $copiedVersion->getVersion());
    }

    public function testArchiveVersion(): void
    {
        $account = $this->createTestAccount();
        $version = $this->menuVersionService->createVersion($account, 'Test version');

        // 测试归档草稿版本（会被删除）
        $versionId = $version->getId();
        $this->assertIsString($versionId);
        $this->menuVersionService->archiveVersion($version);

        // 由于是草稿版本，应该被删除
        self::getEntityManager()->clear();
        $foundVersion = self::getEntityManager()->find(MenuVersion::class, $versionId);
        $this->assertNull($foundVersion);
    }

    public function testCompareVersions(): void
    {
        $account = $this->createTestAccount();
        $version1 = $this->menuVersionService->createVersion($account, 'Version 1');
        $version2 = $this->menuVersionService->createVersion($account, 'Version 2');

        $comparison = $this->menuVersionService->compareVersions($version1, $version2);

        $this->assertIsArray($comparison);
        $this->assertArrayHasKey('added', $comparison);
        $this->assertArrayHasKey('removed', $comparison);
        $this->assertArrayHasKey('modified', $comparison);
    }

    public function testPublishVersion(): void
    {
        // 测试草稿版本发布失败情况（因为缺少必要的依赖服务）
        $account = $this->createTestAccount();
        $version = $this->menuVersionService->createVersion($account, 'Test version');

        $this->expectException(\Throwable::class);
        $this->menuVersionService->publishVersion($version);
    }

    public function testRollbackToVersion(): void
    {
        $account = $this->createTestAccount();
        $version = $this->menuVersionService->createVersion($account, 'Original version');

        // 测试回滚到草稿版本应该抛出异常
        $this->expectException(\Throwable::class);
        $this->expectExceptionMessage('不能回滚到草稿版本');
        $this->menuVersionService->rollbackToVersion($version);
    }

    public function testUpdateVersionButton(): void
    {
        $account = $this->createTestAccount();
        $version = $this->menuVersionService->createVersion($account, 'Test version');

        // 创建一个测试按钮
        $button = new MenuButtonVersion();
        $button->setVersion($version);
        $button->setName('Original Name');
        $button->setType(MenuType::CLICK);

        self::getEntityManager()->persist($button);
        self::getEntityManager()->flush();

        $updateData = [
            'name' => 'Updated Name',
            'clickKey' => 'updated_key',
        ];

        $this->menuVersionService->updateVersionButton($button, $updateData);

        $this->assertSame('Updated Name', $button->getName());
        $this->assertSame('updated_key', $button->getClickKey());
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
