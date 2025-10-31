<?php

namespace WechatOfficialAccountMenuBundle\Request\Menu;

use WechatOfficialAccountBundle\Request\WithAccountRequest;

/**
 * 测试个性化菜单匹配结果请求
 *
 * @see https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Personalized_menu_interface.html
 * @see https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Personalized_menu_interface/User_personalized_menu_matching_results.html
 */
class TryMatchMenuRequest extends WithAccountRequest
{
    private string $userId;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    public function getApiName(): string
    {
        return 'cgi-bin/menu/trymatch';
    }

    public function getHttpMethod(): string
    {
        return 'POST';
    }

    /**
     * @return array<string, mixed>
     */
    public function getRequestData(): array
    {
        return [
            'user_id' => $this->userId,
        ];
    }

    public function getRequestPath(): string
    {
        return 'https://api.weixin.qq.com/cgi-bin/menu/trymatch';
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
                'user_id' => $this->userId,
            ],
        ];
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }
}
