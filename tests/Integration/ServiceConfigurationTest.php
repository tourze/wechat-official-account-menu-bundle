<?php

namespace WechatOfficialAccountMenuBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use WechatOfficialAccountMenuBundle\Repository\MenuButtonRepository;

class ServiceConfigurationTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return IntegrationTestKernel::class;
    }

    public function testRepositoryIsRegisteredAsService(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        
        $this->assertTrue($container->has(MenuButtonRepository::class));
        $repository = $container->get(MenuButtonRepository::class);
        $this->assertInstanceOf(MenuButtonRepository::class, $repository);
    }
} 