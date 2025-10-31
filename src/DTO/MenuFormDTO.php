<?php

namespace WechatOfficialAccountMenuBundle\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use WechatOfficialAccountMenuBundle\Enum\MenuType;

class MenuFormDTO
{
    #[Assert\NotBlank(message: '菜单名称不能为空')]
    #[Assert\Length(max: 60, maxMessage: '菜单名称最多60个字符')]
    public ?string $name = null;

    public ?MenuType $type = null;

    public ?string $parentId = null;

    #[Assert\Length(max: 128, maxMessage: 'Key值最多128个字符')]
    public ?string $clickKey = null;

    #[Assert\Length(max: 1024, maxMessage: 'URL最多1024个字符')]
    #[Assert\Url(message: 'URL格式不正确')]
    public ?string $url = null;

    #[Assert\Length(max: 120, maxMessage: 'AppID最多120个字符')]
    public ?string $appId = null;

    public ?string $pagePath = null;

    #[Assert\PositiveOrZero(message: '排序位置必须为非负数')]
    public int $position = 0;

    public bool $enabled = true;

    /**
     * @return array{name: string|null, type: MenuType|null, clickKey: string|null, url: string|null, appId: string|null, pagePath: string|null, position: int, enabled: bool}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'clickKey' => $this->clickKey,
            'url' => $this->url,
            'appId' => $this->appId,
            'pagePath' => $this->pagePath,
            'position' => $this->position,
            'enabled' => $this->enabled,
        ];
    }
}
