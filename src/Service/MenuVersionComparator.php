<?php

namespace WechatOfficialAccountMenuBundle\Service;

use WechatOfficialAccountMenuBundle\Entity\MenuButtonVersion;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;

/**
 * 菜单版本对比服务
 */
final class MenuVersionComparator
{
    /**
     * 对比两个版本
     *
     * @return array{added: array<int, array<string, mixed>>, removed: array<int, array<string, mixed>>, modified: array<int, array<string, mixed>>}
     */
    public function compareVersions(MenuVersion $version1, MenuVersion $version2): array
    {
        $buttons1 = $this->getButtonsMap($version1);
        $buttons2 = $this->getButtonsMap($version2);

        return [
            'added' => $this->findAddedButtons($buttons1, $buttons2),
            'removed' => $this->findRemovedButtons($buttons1, $buttons2),
            'modified' => $this->findModifiedButtons($buttons1, $buttons2),
        ];
    }

    /**
     * 获取版本的按钮映射
     *
     * @return array<string, MenuButtonVersion>
     */
    private function getButtonsMap(MenuVersion $version): array
    {
        $map = [];
        foreach ($version->getButtons() as $button) {
            $originalId = $button->getOriginalButtonId() ?? $button->getId();
            $map[$originalId] = $button;
        }

        return $map;
    }

    /**
     * @param array<string, MenuButtonVersion> $buttons1
     * @param array<string, MenuButtonVersion> $buttons2
     * @return array<int, array<string, mixed>>
     */
    private function findAddedButtons(array $buttons1, array $buttons2): array
    {
        $added = [];
        foreach ($buttons2 as $id => $button) {
            if (!isset($buttons1[$id])) {
                $added[] = $this->formatButtonInfo($button);
            }
        }

        return $added;
    }

    /**
     * 格式化按钮信息
     *
     * @return array{id: string, name: string, type: string|null, parent: string|null}
     */
    private function formatButtonInfo(MenuButtonVersion $button): array
    {
        return [
            'id' => $button->getOriginalButtonId() ?? $button->getId() ?? '',
            'name' => $button->getName() ?? '',
            'type' => $button->getType()?->getLabel(),
            'parent' => $button->getParent()?->getName(),
        ];
    }

    /**
     * @param array<string, MenuButtonVersion> $buttons1
     * @param array<string, MenuButtonVersion> $buttons2
     * @return array<int, array<string, mixed>>
     */
    private function findRemovedButtons(array $buttons1, array $buttons2): array
    {
        $removed = [];
        foreach ($buttons1 as $id => $button) {
            if (!isset($buttons2[$id])) {
                $removed[] = $this->formatButtonInfo($button);
            }
        }

        return $removed;
    }

    /**
     * @param array<string, MenuButtonVersion> $buttons1
     * @param array<string, MenuButtonVersion> $buttons2
     * @return array<int, array<string, mixed>>
     */
    private function findModifiedButtons(array $buttons1, array $buttons2): array
    {
        $modified = [];
        foreach ($buttons1 as $id => $button1) {
            if (isset($buttons2[$id])) {
                $button2 = $buttons2[$id];
                if ($this->isButtonModified($button1, $button2)) {
                    $modified[] = [
                        'id' => $id,
                        'name' => $button1->getName(),
                        'changes' => $this->getButtonChanges($button1, $button2),
                    ];
                }
            }
        }

        return $modified;
    }

    /**
     * 检查按钮是否被修改
     */
    private function isButtonModified(MenuButtonVersion $button1, MenuButtonVersion $button2): bool
    {
        return $button1->getName() !== $button2->getName()
            || $button1->getType() !== $button2->getType()
            || $button1->getClickKey() !== $button2->getClickKey()
            || $button1->getUrl() !== $button2->getUrl()
            || $button1->getAppId() !== $button2->getAppId()
            || $button1->getPagePath() !== $button2->getPagePath()
            || $button1->getMediaId() !== $button2->getMediaId()
            || $button1->isEnabled() !== $button2->isEnabled();
    }

    /**
     * 获取按钮的变更内容
     *
     * @return array<string, array{old: mixed, new: mixed}>
     */
    private function getButtonChanges(MenuButtonVersion $button1, MenuButtonVersion $button2): array
    {
        $changes = [];

        if ($button1->getName() !== $button2->getName()) {
            $changes['name'] = [
                'old' => $button1->getName(),
                'new' => $button2->getName(),
            ];
        }

        if ($button1->getType() !== $button2->getType()) {
            $changes['type'] = [
                'old' => $button1->getType()?->getLabel(),
                'new' => $button2->getType()?->getLabel(),
            ];
        }

        if ($button1->getUrl() !== $button2->getUrl()) {
            $changes['url'] = [
                'old' => $button1->getUrl(),
                'new' => $button2->getUrl(),
            ];
        }

        if ($button1->isEnabled() !== $button2->isEnabled()) {
            $changes['enabled'] = [
                'old' => $button1->isEnabled(),
                'new' => $button2->isEnabled(),
            ];
        }

        return $changes;
    }
}
