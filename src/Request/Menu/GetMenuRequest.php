<?php

namespace WechatOfficialAccountMenuBundle\Request\Menu;

use WechatOfficialAccountBundle\Request\WithAccountRequest;

/**
 * 查询自定义菜单请求
 *
 * @see https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Querying_Custom_Menus.html
 */
class GetMenuRequest extends WithAccountRequest
{
    public function getApiName(): string
    {
        return 'cgi-bin/get_current_selfmenu_info';
    }

    public function getHttpMethod(): string
    {
        return 'GET';
    }

    /**
     * @return array<string, mixed>
     */
    public function getRequestData(): array
    {
        return [];
    }

    public function getRequestPath(): string
    {
        return 'https://api.weixin.qq.com/cgi-bin/menu/get';
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
