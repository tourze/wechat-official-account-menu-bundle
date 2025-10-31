<?php

namespace WechatOfficialAccountMenuBundle\Tests\DTO;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WechatOfficialAccountMenuBundle\DTO\MenuFormDTO;
use WechatOfficialAccountMenuBundle\Enum\MenuType;

/**
 * @internal
 */
#[CoversClass(MenuFormDTO::class)]
final class MenuFormDTOTest extends TestCase
{
    private MenuFormDTO $dto;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dto = new MenuFormDTO();
    }

    public function testDtoCanBeInstantiated(): void
    {
        $this->assertInstanceOf(MenuFormDTO::class, $this->dto);
    }

    public function testDefaultValues(): void
    {
        $this->assertNull($this->dto->name);
        $this->assertNull($this->dto->type);
        $this->assertNull($this->dto->parentId);
        $this->assertNull($this->dto->clickKey);
        $this->assertNull($this->dto->url);
        $this->assertNull($this->dto->appId);
        $this->assertNull($this->dto->pagePath);
        $this->assertSame(0, $this->dto->position);
        $this->assertTrue($this->dto->enabled);
    }

    public function testToArray(): void
    {
        $this->dto->name = 'Test Menu';
        $this->dto->type = MenuType::CLICK;
        $this->dto->clickKey = 'test_key';
        $this->dto->position = 1;
        $this->dto->enabled = false;

        $array = $this->dto->toArray();

        $this->assertSame('Test Menu', $array['name']);
        $this->assertSame(MenuType::CLICK, $array['type']);
        $this->assertSame('test_key', $array['clickKey']);
        $this->assertNull($array['url']);
        $this->assertNull($array['appId']);
        $this->assertNull($array['pagePath']);
        $this->assertSame(1, $array['position']);
        $this->assertFalse($array['enabled']);
    }
}
