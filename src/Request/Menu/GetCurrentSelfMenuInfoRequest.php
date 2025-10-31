<?php

namespace WechatOfficialAccountMenuBundle\Request\Menu;

use WechatOfficialAccountBundle\Request\WithAccountRequest;

/**
 * 获取自定义菜单配置请求
 *
 * 本接口将会提供公众号当前使用的自定义菜单的配置，如果公众号是通过API调用设置的菜单，
 * 则返回菜单的开发配置，而如果公众号是在公众平台官网通过网站功能发布菜单，
 * 则本接口返回运营者设置的菜单配置。
 *
 * @see https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Getting_Custom_Menu_Configurations.html
 */
class GetCurrentSelfMenuInfoRequest extends WithAccountRequest
{
    public function getRequestPath(): string
    {
        return 'https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info';
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
