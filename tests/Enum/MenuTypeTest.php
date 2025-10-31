<?php

namespace WechatOfficialAccountMenuBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;
use WechatOfficialAccountMenuBundle\Enum\MenuType;

/**
 * @internal
 */
#[CoversClass(MenuType::class)]
final class MenuTypeTest extends AbstractEnumTestCase
{
    #[TestWith([MenuType::NONE, 'none', '无'])]
    #[TestWith([MenuType::VIEW, 'view', '跳转 URL'])]
    #[TestWith([MenuType::CLICK, 'click', '点击推事件'])]
    #[TestWith([MenuType::MINI_PROGRAM, 'miniprogram', '小程序'])]
    #[TestWith([MenuType::SCAN_CODE_PUSH, 'scancode_push', '扫码推事件'])]
    #[TestWith([MenuType::SCAN_CODE_WAIT_MSG, 'scancode_waitmsg', '扫码带提示'])]
    #[TestWith([MenuType::PIC_SYS_PHOTO, 'pic_sysphoto', '系统拍照发图'])]
    #[TestWith([MenuType::PIC_PHOTO_ALBUM, 'pic_photo_or_album', '拍照或者相册发图'])]
    #[TestWith([MenuType::PIC_WEIXIN, 'pic_weixin', '微信相册发图'])]
    #[TestWith([MenuType::LOCATION_SELECT, 'location_select', '发送位置'])]
    public function testValueAndLabelShouldBeCorrect(MenuType $enum, string $expectedValue, string $expectedLabel): void
    {
        $this->assertEquals($expectedValue, $enum->value);
        $this->assertEquals($expectedLabel, $enum->getLabel());
    }

    public function testValuesShouldBeUnique(): void
    {
        $cases = MenuType::cases();
        $values = array_map(fn (MenuType $case) => $case->value, $cases);

        $this->assertEquals(array_unique($values), $values, 'Enum values should be unique');
        $this->assertCount(10, array_unique($values));
    }

    public function testLabelsShouldBeUnique(): void
    {
        $cases = MenuType::cases();
        $labels = array_map(fn (MenuType $case) => $case->getLabel(), $cases);

        $this->assertEquals(array_unique($labels), $labels, 'Enum labels should be unique');
        $this->assertCount(10, array_unique($labels));
    }

    public function testToSelectItemShouldReturnCorrectFormat(): void
    {
        $selectItem = MenuType::VIEW->toSelectItem();

        $this->assertIsArray($selectItem);
        $this->assertArrayHasKey('label', $selectItem);
        $this->assertArrayHasKey('value', $selectItem);
        $this->assertEquals('跳转 URL', $selectItem['label']);
        $this->assertEquals('view', $selectItem['value']);
    }

    public function testAllCasesShouldBeAvailable(): void
    {
        $cases = MenuType::cases();

        $this->assertCount(10, $cases);
        $this->assertContainsOnlyInstancesOf(MenuType::class, $cases);

        $expectedCases = [
            MenuType::NONE,
            MenuType::VIEW,
            MenuType::CLICK,
            MenuType::MINI_PROGRAM,
            MenuType::SCAN_CODE_PUSH,
            MenuType::SCAN_CODE_WAIT_MSG,
            MenuType::PIC_SYS_PHOTO,
            MenuType::PIC_PHOTO_ALBUM,
            MenuType::PIC_WEIXIN,
            MenuType::LOCATION_SELECT,
        ];

        $this->assertEquals($expectedCases, $cases);
    }

    public function testToArrayShouldReturnArrayWithValueAndLabel(): void
    {
        $result = MenuType::CLICK->toArray();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertEquals('click', $result['value']);
        $this->assertEquals('点击推事件', $result['label']);
    }
}
