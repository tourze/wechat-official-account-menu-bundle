<?php

namespace WechatOfficialAccountMenuBundle\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class MenuSortDTO
{
    /**
     * @var array<int, array{id: string, position: int}>
     */
    #[Assert\NotBlank(message: '排序数据不能为空')]
    #[Assert\Type(type: 'array', message: '排序数据必须为数组')]
    #[Assert\Count(min: 1, minMessage: '至少需要一个菜单项')]
    #[Assert\All(
        constraints: [
            new Assert\Collection(fields: [
                'id' => [
                    new Assert\NotBlank(message: '菜单ID不能为空'),
                    new Assert\Type(type: 'string', message: '菜单ID必须为字符串'),
                ],
                'position' => [
                    new Assert\NotBlank(message: '位置不能为空'),
                    new Assert\Type(type: 'int', message: '位置必须为整数'),
                    new Assert\PositiveOrZero(message: '位置必须为非负数'),
                ],
            ]),
        ]
    )]
    public array $items = [];

    /**
     * @param array<int, array{id: string, position: int}> $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * 获取排序映射.
     *
     * @return array<string, int>
     */
    public function getPositionMap(): array
    {
        $map = [];
        foreach ($this->items as $item) {
            $map[$item['id']] = $item['position'];
        }

        return $map;
    }
}
