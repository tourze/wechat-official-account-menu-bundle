<?php

namespace WechatOfficialAccountMenuBundle\Tests\DTO;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WechatOfficialAccountMenuBundle\DTO\MenuSortDTO;

/**
 * @internal
 */
#[CoversClass(MenuSortDTO::class)]
final class MenuSortDTOTest extends TestCase
{
    public function testCanCreateMenuSortDto(): void
    {
        $data = [
            ['id' => '1', 'position' => 10],
            ['id' => '2', 'position' => 20],
        ];

        $dto = new MenuSortDTO($data);

        $this->assertInstanceOf(MenuSortDTO::class, $dto);
        $this->assertEquals($data, $dto->items);
    }

    public function testCanCreateEmptyMenuSortDto(): void
    {
        $dto = new MenuSortDTO([]);

        $this->assertInstanceOf(MenuSortDTO::class, $dto);
        $this->assertEquals([], $dto->items);
    }

    public function testCanAccessItemsProperty(): void
    {
        $data = [
            ['id' => '1', 'position' => 10],
            ['id' => '2', 'position' => 20],
        ];

        $dto = new MenuSortDTO($data);

        $this->assertIsArray($dto->items);
        $this->assertCount(2, $dto->items);
        $this->assertEquals('1', $dto->items[0]['id']);
        $this->assertEquals(10, $dto->items[0]['position']);
    }
}
