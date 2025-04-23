<?php

namespace WechatOfficialAccountMenuBundle\Request;

use WechatOfficialAccountBundle\Request\WithAccountRequest;

/**
 * 个性化菜单接口
 *
 * @see https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Personalized_menu_interface.html
 */
class AddConditionalMenuRequest extends WithAccountRequest
{
    /**
     * @var array 一级菜单数组，个数应为1~3个
     */
    private array $buttons;

    private array $matchRule;

    public function getRequestPath(): string
    {
        return 'https://api.weixin.qq.com/cgi-bin/menu/addconditional';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'json' => [
                'button' => $this->getButtons(),
                'matchrule' => $this->getMatchRule(),
            ],
        ];
    }

    public function getButtons(): array
    {
        return $this->buttons;
    }

    public function setButtons(array $buttons): void
    {
        $this->buttons = $buttons;
    }

    public function getMatchRule(): array
    {
        return $this->matchRule;
    }

    public function setMatchRule(array $matchRule): void
    {
        $this->matchRule = $matchRule;
    }
}
