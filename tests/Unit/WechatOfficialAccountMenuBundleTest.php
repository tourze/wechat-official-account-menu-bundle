<?php

namespace WechatOfficialAccountMenuBundle\Tests\Unit;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use PHPUnit\Framework\TestCase;
use WechatOfficialAccountBundle\WechatOfficialAccountBundle;
use WechatOfficialAccountMenuBundle\WechatOfficialAccountMenuBundle;

class WechatOfficialAccountMenuBundleTest extends TestCase
{
    public function testGetBundleDependencies(): void
    {
        $bundle = new WechatOfficialAccountMenuBundle();
        
        $expected = [
            DoctrineBundle::class => ['all' => true],
            WechatOfficialAccountBundle::class => ['all' => true],
        ];
        
        $this->assertEquals($expected, WechatOfficialAccountMenuBundle::getBundleDependencies());
    }
}