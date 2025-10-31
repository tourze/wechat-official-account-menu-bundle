<?php

declare(strict_types=1);

namespace WechatOfficialAccountMenuBundle\Tests\Test;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * 测试用的内存用户提供者
 *
 * @internal
 *
 * @implements UserProviderInterface<TestMemoryUser>
 */
final class TestMemoryUserProvider implements UserProviderInterface
{
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof TestMemoryUser) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        // 对于测试用户，直接返回相同的用户
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return TestMemoryUser::class === $class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // 创建一个基本的测试用户
        /** @var non-empty-string $identifier */
        return new TestMemoryUser($identifier, ['ROLE_USER']);
    }
}
