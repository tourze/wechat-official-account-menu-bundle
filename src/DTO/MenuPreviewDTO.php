<?php

namespace WechatOfficialAccountMenuBundle\DTO;

use WechatOfficialAccountMenuBundle\Entity\MenuButton;
use WechatOfficialAccountMenuBundle\Entity\MenuButtonVersion;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;

class MenuPreviewDTO implements \JsonSerializable
{
    /** @var array<int, array<string, mixed>> */
    private array $buttons = [];

    /** @var array<string, int> */
    private array $statistics = [
        'totalButtons' => 0,
        'rootButtons' => 0,
        'subButtons' => 0,
        'enabledButtons' => 0,
        'disabledButtons' => 0,
    ];

    /** @var array<int, string> */
    private array $errors = [];

    private bool $valid = true;

    /**
     * 从MenuButton数组创建预览.
     *
     * @param MenuButton[] $rootButtons
     */
    public static function fromMenuButtons(array $rootButtons): self
    {
        $dto = new self();

        foreach ($rootButtons as $button) {
            if ($button->isEnabled()) {
                $dto->buttons[] = $button->toWechatFormat();
            }
            $dto->updateStatistics($button);
        }

        $dto->validateStructure();

        return $dto;
    }

    /**
     * 从MenuVersion创建预览.
     */
    public static function fromMenuVersion(MenuVersion $version): self
    {
        $dto = new self();

        foreach ($version->getRootButtons() as $button) {
            if ($button->isEnabled()) {
                $dto->buttons[] = $button->toWechatFormat();
            }
            $dto->updateStatisticsForVersion($button);
        }

        $dto->validateStructure();

        return $dto;
    }

    /**
     * 更新统计信息.
     */
    private function updateStatistics(MenuButton $button): void
    {
        ++$this->statistics['totalButtons'];

        if (null === $button->getParent()) {
            ++$this->statistics['rootButtons'];
        } else {
            ++$this->statistics['subButtons'];
        }

        if ($button->isEnabled()) {
            ++$this->statistics['enabledButtons'];
        } else {
            ++$this->statistics['disabledButtons'];
        }

        foreach ($button->getChildren() as $child) {
            $this->updateStatistics($child);
        }
    }

    /**
     * 更新版本按钮统计信息.
     */
    private function updateStatisticsForVersion(MenuButtonVersion $button): void
    {
        ++$this->statistics['totalButtons'];

        if (null === $button->getParent()) {
            ++$this->statistics['rootButtons'];
        } else {
            ++$this->statistics['subButtons'];
        }

        if ($button->isEnabled()) {
            ++$this->statistics['enabledButtons'];
        } else {
            ++$this->statistics['disabledButtons'];
        }

        foreach ($button->getChildren() as $child) {
            $this->updateStatisticsForVersion($child);
        }
    }

    /**
     * 验证菜单结构.
     */
    private function validateStructure(): void
    {
        // 检查一级菜单数量
        if (count($this->buttons) > 3) {
            $this->errors[] = '一级菜单最多3个';
            $this->valid = false;
        }

        // 检查每个一级菜单的子菜单数量
        foreach ($this->buttons as $button) {
            /** @var array<string, mixed> $button */
            if (isset($button['sub_button'])) {
                $subButton = $button['sub_button'];
                /** @var array<string, mixed> $subButton */
                if (count($subButton) > 5) {
                    $name = $button['name'] ?? 'unknown';
                    assert(is_string($name));
                    $this->errors[] = sprintf('菜单"%s"的子菜单最多5个', $name);
                    $this->valid = false;
                }
            }
        }

        // 检查是否为空
        if (0 === count($this->buttons)) {
            $this->errors[] = '没有启用的菜单';
            $this->valid = false;
        }
    }

    /**
     * @return array{buttons: array<int, array<string, mixed>>, statistics: array<string, int>, errors: array<int, string>, valid: bool, wechatFormat: array{button: array<int, array<string, mixed>>}}
     */
    public function jsonSerialize(): array
    {
        return [
            'buttons' => $this->buttons,
            'statistics' => $this->statistics,
            'errors' => $this->errors,
            'valid' => $this->valid,
            'wechatFormat' => [
                'button' => $this->buttons,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getButtons(): array
    {
        return $this->buttons;
    }

    /**
     * @return array<string, int>
     */
    public function getStatistics(): array
    {
        return $this->statistics;
    }

    /**
     * @return array<int, string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * @return array{button: array<int, array<string, mixed>>}
     */
    public function getWechatFormat(): array
    {
        return [
            'button' => $this->buttons,
        ];
    }
}
