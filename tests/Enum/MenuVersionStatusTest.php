<?php

namespace WechatOfficialAccountMenuBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;
use WechatOfficialAccountMenuBundle\Enum\MenuVersionStatus;

/**
 * @internal
 */
#[CoversClass(MenuVersionStatus::class)]
final class MenuVersionStatusTest extends AbstractEnumTestCase
{
    #[TestWith([MenuVersionStatus::DRAFT, 'draft', '草稿'])]
    #[TestWith([MenuVersionStatus::PUBLISHED, 'published', '已发布'])]
    #[TestWith([MenuVersionStatus::ARCHIVED, 'archived', '已归档'])]
    public function testValueAndLabelShouldBeCorrect(MenuVersionStatus $enum, string $expectedValue, string $expectedLabel): void
    {
        $this->assertEquals($expectedValue, $enum->value);
        $this->assertEquals($expectedLabel, $enum->getLabel());
    }

    public function testValuesShouldBeUnique(): void
    {
        $cases = MenuVersionStatus::cases();
        $values = array_map(fn (MenuVersionStatus $case) => $case->value, $cases);

        $this->assertEquals(array_unique($values), $values, 'Enum values should be unique');
        $this->assertCount(3, array_unique($values));
    }

    public function testLabelsShouldBeUnique(): void
    {
        $cases = MenuVersionStatus::cases();
        $labels = array_map(fn (MenuVersionStatus $case) => $case->getLabel(), $cases);

        $this->assertEquals(array_unique($labels), $labels, 'Enum labels should be unique');
        $this->assertCount(3, array_unique($labels));
    }

    public function testToSelectItemShouldReturnCorrectFormat(): void
    {
        $selectItem = MenuVersionStatus::PUBLISHED->toSelectItem();

        $this->assertIsArray($selectItem);
        $this->assertArrayHasKey('label', $selectItem);
        $this->assertArrayHasKey('value', $selectItem);
        $this->assertEquals('已发布', $selectItem['label']);
        $this->assertEquals('published', $selectItem['value']);
    }

    public function testToArrayShouldReturnArrayWithValueAndLabel(): void
    {
        $result = MenuVersionStatus::DRAFT->toArray();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertEquals('draft', $result['value']);
        $this->assertEquals('草稿', $result['label']);
    }

    public function testAllCasesShouldBeAvailable(): void
    {
        $cases = MenuVersionStatus::cases();

        $this->assertCount(3, $cases);
        $this->assertContainsOnlyInstancesOf(MenuVersionStatus::class, $cases);

        $expectedCases = [
            MenuVersionStatus::DRAFT,
            MenuVersionStatus::PUBLISHED,
            MenuVersionStatus::ARCHIVED,
        ];

        $this->assertEquals($expectedCases, $cases);
    }

    public function testInterfaceImplementationShouldBeCorrect(): void
    {
        $status = MenuVersionStatus::DRAFT;

        $this->assertInstanceOf(Labelable::class, $status);
        $this->assertInstanceOf(Itemable::class, $status);
        $this->assertInstanceOf(Selectable::class, $status);
    }
}
