<?php

namespace WechatOfficialAccountMenuBundle\Tests\DTO;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WechatOfficialAccountMenuBundle\DTO\MenuPreviewDTO;

/**
 * @internal
 */
#[CoversClass(MenuPreviewDTO::class)]
final class MenuPreviewDTOTest extends TestCase
{
    public function testEmptyDtoValidation(): void
    {
        $dto = $this->createMenuPreviewDTO();

        $result = $dto->jsonSerialize();

        $this->assertArrayHasKey('buttons', $result);
        $this->assertArrayHasKey('statistics', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('valid', $result);
        $this->assertArrayHasKey('wechatFormat', $result);

        $this->assertIsArray($result['buttons']);
        $this->assertIsArray($result['statistics']);
        $this->assertIsArray($result['errors']);
        $this->assertIsBool($result['valid']);
        $this->assertIsArray($result['wechatFormat']);
    }

    public function testGetters(): void
    {
        $dto = $this->createMenuPreviewDTO();

        $this->assertIsArray($dto->getButtons());
        $this->assertIsArray($dto->getStatistics());
        $this->assertIsArray($dto->getErrors());
        $this->assertIsBool($dto->isValid());
        $this->assertIsArray($dto->getWechatFormat());
        $this->assertArrayHasKey('button', $dto->getWechatFormat());
    }

    private function createMenuPreviewDTO(): MenuPreviewDTO
    {
        // 使用反射创建实例，因为构造函数是private
        $reflection = new \ReflectionClass(MenuPreviewDTO::class);

        return $reflection->newInstanceWithoutConstructor();
    }
}
