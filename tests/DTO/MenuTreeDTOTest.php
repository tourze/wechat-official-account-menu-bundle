<?php

namespace WechatOfficialAccountMenuBundle\Tests\DTO;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WechatOfficialAccountMenuBundle\DTO\MenuTreeDTO;

/**
 * @internal
 */
#[CoversClass(MenuTreeDTO::class)]
final class MenuTreeDTOTest extends TestCase
{
    public function testCanCreateMenuTreeDto(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Test Menu',
            'children' => [],
        ];

        $dto = new MenuTreeDTO($data);

        $this->assertInstanceOf(MenuTreeDTO::class, $dto);
        $this->assertEquals($data, $dto->data);
    }

    public function testCanCreateEmptyMenuTreeDto(): void
    {
        $dto = new MenuTreeDTO([]);

        $this->assertInstanceOf(MenuTreeDTO::class, $dto);
        $this->assertEquals([], $dto->data);
    }

    public function testCanAccessDataProperty(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Test Menu',
            'children' => [
                ['id' => 2, 'name' => 'Child Menu', 'children' => []],
            ],
        ];

        $dto = new MenuTreeDTO($data);

        $this->assertIsArray($dto->data);
        $this->assertEquals(1, $dto->data['id']);
        $this->assertEquals('Test Menu', $dto->data['name']);
        $this->assertIsArray($dto->data['children']);
        $this->assertCount(1, $dto->data['children']);
    }
}
