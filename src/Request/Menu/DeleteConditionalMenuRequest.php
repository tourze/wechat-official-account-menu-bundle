<?php

namespace WechatOfficialAccountMenuBundle\Request\Menu;

use WechatOfficialAccountBundle\Request\WithAccountRequest;

/**
 * 删除个性化菜单请求
 *
 * @see https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Personalized_menu_interface.html
 * @see https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Personalized_menu_interface/Delete_conditional_menu.html
 */
class DeleteConditionalMenuRequest extends WithAccountRequest
{
    private string $menuId;

    public function getRequestPath(): string
    {
        return 'https://api.weixin.qq.com/cgi-bin/menu/delconditional';
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
            'json' => [
                'menuid' => $this->menuId,
            ],
        ];
    }

    public function getMenuId(): string
    {
        return $this->menuId;
    }

    public function setMenuId(string $menuId): void
    {
        $this->menuId = $menuId;
    }
}
