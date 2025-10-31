<?php

namespace WechatOfficialAccountMenuBundle\Request\Menu;

use WechatOfficialAccountBundle\Request\WithAccountRequest;

/**
 * 删除自定义菜单请求
 *
 * @see https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Deleting_Custom-Defined_Menu.html
 */
class DeleteMenuRequest extends WithAccountRequest
{
    public function getRequestPath(): string
    {
        return 'https://api.weixin.qq.com/cgi-bin/menu/delete';
    }

    public function getRequestMethod(): string
    {
        return 'GET';
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getRequestOptions(): ?array
    {
        return null;
    }
}
