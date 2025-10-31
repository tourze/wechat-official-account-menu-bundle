<?php

namespace WechatOfficialAccountMenuBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 菜单类型
 */
enum MenuType: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case NONE = 'none';
    case VIEW = 'view';
    case CLICK = 'click';
    case MINI_PROGRAM = 'miniprogram';
    case SCAN_CODE_PUSH = 'scancode_push';
    case SCAN_CODE_WAIT_MSG = 'scancode_waitmsg';
    case PIC_SYS_PHOTO = 'pic_sysphoto';
    case PIC_PHOTO_ALBUM = 'pic_photo_or_album';
    case PIC_WEIXIN = 'pic_weixin';
    case LOCATION_SELECT = 'location_select';

    public function getLabel(): string
    {
        return match ($this) {
            self::NONE => '无',
            self::VIEW => '跳转 URL',
            self::CLICK => '点击推事件',
            self::MINI_PROGRAM => '小程序',
            self::SCAN_CODE_PUSH => '扫码推事件',
            self::SCAN_CODE_WAIT_MSG => '扫码带提示',
            self::PIC_SYS_PHOTO => '系统拍照发图',
            self::PIC_PHOTO_ALBUM => '拍照或者相册发图',
            self::PIC_WEIXIN => '微信相册发图',
            self::LOCATION_SELECT => '发送位置',
        };
    }
}
