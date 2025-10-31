<?php

namespace WechatOfficialAccountMenuBundle\Request\Menu;

use WechatOfficialAccountBundle\Request\WithAccountRequest;

/**
 * 创建个性化菜单请求
 *
 * @see https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Personalized_menu_interface.html
 * @see https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Personalized_menu_interface/Conditional_menu_creation.html
 */
class AddConditionalMenuRequest extends WithAccountRequest
{
    /**
     * @var array<string, mixed>
     */
    private array $menuData;

    /**
     * @var array<string, mixed>
     */
    private array $matchRule;

    public function getRequestPath(): string
    {
        return 'https://api.weixin.qq.com/cgi-bin/menu/addconditional';
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
        $data = array_merge($this->menuData, ['matchrule' => $this->matchRule]);

        return [
            'json' => $data,
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

    /**
     * @return array<string, mixed>
     */
    public function getMatchRule(): array
    {
        return $this->matchRule;
    }

    /**
     * @param array<string, mixed> $matchRule
     */
    public function setMatchRule(array $matchRule): void
    {
        $this->matchRule = $matchRule;
    }
}
