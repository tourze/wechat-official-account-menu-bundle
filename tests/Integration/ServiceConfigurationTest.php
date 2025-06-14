<?php

namespace WechatOfficialAccountMenuBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Tourze\IntegrationTestKernel\IntegrationTestKernel;
use WechatOfficialAccountMenuBundle\Repository\MenuButtonRepository;
use WechatOfficialAccountMenuBundle\WechatOfficialAccountMenuBundle;

class ServiceConfigurationTest extends KernelTestCase
{
    protected static function createKernel(array $options = []): KernelInterface
    {
        $env = $options['environment'] ?? $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'test';
        $debug = $options['debug'] ?? $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? true;

        return new IntegrationTestKernel($env, $debug, [
            WechatOfficialAccountMenuBundle::class => ['all' => true],
        ]);
    }

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testRepositoryIsRegisteredAsService(): void
    {
        $container = self::getContainer();
        
        $this->assertTrue($container->has(MenuButtonRepository::class));
        $repository = $container->get(MenuButtonRepository::class);
        $this->assertInstanceOf(MenuButtonRepository::class, $repository);
    }
} 