<?php

namespace WechatOfficialAccountMenuBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;
use WechatOfficialAccountMenuBundle\Service\MenuVersionComparator;

/**
 * @internal
 */
#[CoversClass(MenuVersionComparator::class)]
final class MenuVersionComparatorTest extends TestCase
{
    private MenuVersionComparator $comparator;

    public function testCompareVersionsReturnsCorrectStructure(): void
    {
        $version1 = new MenuVersion();
        $version2 = new MenuVersion();

        $result = $this->comparator->compareVersions($version1, $version2);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('added', $result);
        $this->assertArrayHasKey('removed', $result);
        $this->assertArrayHasKey('modified', $result);
    }

    public function testCompareVersionsWithEmptyVersions(): void
    {
        $version1 = new MenuVersion();
        $version2 = new MenuVersion();

        $result = $this->comparator->compareVersions($version1, $version2);

        $this->assertEmpty($result['added']);
        $this->assertEmpty($result['removed']);
        $this->assertEmpty($result['modified']);
    }

    protected function setUp(): void
    {
        $this->comparator = new MenuVersionComparator();
    }
}
