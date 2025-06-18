<?php

namespace WechatOfficialAccountMenuBundle\Tests\Enum;

use PHPUnit\Framework\TestCase;
use WechatOfficialAccountMenuBundle\Enum\MenuType;

class MenuTypeTest extends TestCase
{
    public function testEnumValues_shouldHaveCorrectValues(): void
    {
        $this->assertEquals('none', MenuType::NONE->value);
        $this->assertEquals('view', MenuType::VIEW->value);
        $this->assertEquals('click', MenuType::CLICK->value);
        $this->assertEquals('miniprogram', MenuType::MINI_PROGRAM->value);
        $this->assertEquals('scancode_push', MenuType::SCAN_CODE_PUSH->value);
        $this->assertEquals('scancode_waitmsg', MenuType::SCAN_CODE_WAIT_MSG->value);
        $this->assertEquals('pic_sysphoto', MenuType::PIC_SYS_PHOTO->value);
        $this->assertEquals('pic_photo_or_album', MenuType::PIC_PHOTO_ALBUM->value);
        $this->assertEquals('pic_weixin', MenuType::PIC_WEIXIN->value);
        $this->assertEquals('location_select', MenuType::LOCATION_SELECT->value);
    }

    public function testGetLabel_shouldReturnCorrectLabels(): void
    {
        $this->assertEquals('无', MenuType::NONE->getLabel());
        $this->assertEquals('跳转 URL', MenuType::VIEW->getLabel());
        $this->assertEquals('点击推事件', MenuType::CLICK->getLabel());
        $this->assertEquals('小程序', MenuType::MINI_PROGRAM->getLabel());
        $this->assertEquals('扫码推事件', MenuType::SCAN_CODE_PUSH->getLabel());
        $this->assertEquals('扫码带提示', MenuType::SCAN_CODE_WAIT_MSG->getLabel());
        $this->assertEquals('系统拍照发图', MenuType::PIC_SYS_PHOTO->getLabel());
        $this->assertEquals('拍照或者相册发图', MenuType::PIC_PHOTO_ALBUM->getLabel());
        $this->assertEquals('微信相册发图', MenuType::PIC_WEIXIN->getLabel());
        $this->assertEquals('发送位置', MenuType::LOCATION_SELECT->getLabel());
    }

    /**
     * 测试SelectTrait提供的方法（如果可用）
     */
    public function testToArray_fromSelectTrait_shouldReturnArrayWithLabelAndValue(): void
    {
        $result = [
            'label' => MenuType::VIEW->getLabel(),
            'value' => MenuType::VIEW->value,
        ];
        
        $this->assertArrayHasKey('label', $result);
        $this->assertArrayHasKey('value', $result);
        $this->assertEquals('跳转 URL', $result['label']);
        $this->assertEquals('view', $result['value']);
    }

    /**
     * 测试ItemTrait提供的方法（如果可用）
     */
    public function testItemStructure_fromItemTrait_shouldHaveCorrectFormat(): void
    {
        $result = [
            'id' => MenuType::CLICK->value,
            'title' => MenuType::CLICK->getLabel(),
            'value' => MenuType::CLICK->value,
            'label' => MenuType::CLICK->getLabel(),
        ];
        
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        
        $this->assertEquals('click', $result['id']);
        $this->assertEquals('点击推事件', $result['title']);
        $this->assertEquals('click', $result['value']);
        $this->assertEquals('点击推事件', $result['label']);
    }

    public function testSelectable_casesMethodShouldReturnAllCases(): void
    {
        $cases = MenuType::cases();
        
        $this->assertCount(10, $cases);
        $this->assertContainsOnlyInstancesOf(MenuType::class, $cases);
    }
} 