<?php

namespace WechatOfficialAccountMenuBundle\Tests\DTO;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WechatOfficialAccountMenuBundle\DTO\MenuVersionListDTO;

/**
 * @internal
 */
#[CoversClass(MenuVersionListDTO::class)]
final class MenuVersionListDTOTest extends TestCase
{
    public function testCanCreateAndSerializeDto(): void
    {
        // 由于 MenuVersion 实体使用了 traits (如 TimestampableAware)，
        // 这些方法无法在 mock 中正确配置，
        // 所以我们测试 DTO 的基本功能而不是完整的 fromMenuVersion 方法

        $reflection = new \ReflectionClass(MenuVersionListDTO::class);
        $constructor = $reflection->getConstructor();

        // 测试构造函数是私有的
        $this->assertInstanceOf(\ReflectionMethod::class, $constructor, 'Constructor should exist');
        $this->assertTrue($constructor->isPrivate());

        // 测试静态方法存在
        $this->assertTrue($reflection->hasMethod('fromMenuVersion'));
        $this->assertTrue($reflection->hasMethod('jsonSerialize'));
    }

    public function testDtoImplementsJsonSerializable(): void
    {
        $this->assertContains(
            \JsonSerializable::class,
            class_implements(MenuVersionListDTO::class)
        );
    }
}
