<?php

declare(strict_types=1);

namespace WechatOfficialAccountMenuBundle\Tests\Test;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * 测试用的简单内存用户
 *
 * @internal
 */
final class TestMemoryUser implements UserInterface
{
    /**
     * @param non-empty-string $username
     * @param list<string> $roles
     */
    public function __construct(
        private string $username,
        private array $roles = ['ROLE_USER'],
    ) {
    }

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function eraseCredentials(): void
    {
    }

    /**
     * @return non-empty-string
     */
    public function getUserIdentifier(): string
    {
        return $this->username;
    }
}
