<?php

namespace WechatOfficialAccountMenuBundle\Request\Menu;

use WechatOfficialAccountBundle\Request\WithAccountRequest;

/**
 * 创建自定义菜单请求
 *
 * @see https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Creating_Custom-Defined_Menu.html
 */
class CreateMenuRequest extends WithAccountRequest
{
    /**
     * @var array<string, mixed>
     */
    private array $menuData;

    public function getRequestPath(): string
    {
        return 'https://api.weixin.qq.com/cgi-bin/menu/create';
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }

    /**
     * @return array<string, mixed>
     */
    public function getRequestOptions(): array
    {
        return [
            'json' => $this->menuData,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getMenuData(): array
    {
        return $this->menuData;
    }

    /**
     * @param array<string, mixed> $menuData
     */
    public function setMenuData(array $menuData): void
    {
        $this->menuData = $menuData;
    }
}
