<?php

declare(strict_types=1);

namespace WechatOfficialAccountMenuBundle\Tests\Test;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * 基础测试类
 * 使用内存用户进行身份验证，避免数据库依赖
 *
 * @internal
 */
#[CoversClass(AbstractTestCase::class)]
#[RunTestsInSeparateProcesses]
abstract class AbstractTestCase extends AbstractEasyAdminControllerTestCase
{
    /**
     * 以管理员身份登录（使用内存用户）
     */
    protected function loginAsAdmin(KernelBrowser $client, string $username = 'admin', string $password = 'password'): UserInterface
    {
        if ('' === $username) {
            $username = 'admin';
        }
        /** @var non-empty-string $username */
        $user = new TestMemoryUser($username, ['ROLE_ADMIN']);
        $client->loginUser($user);

        return $user;
    }
}
