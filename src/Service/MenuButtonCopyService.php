<?php

namespace WechatOfficialAccountMenuBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Entity\MenuButtonVersion;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;
use WechatOfficialAccountMenuBundle\Repository\MenuButtonRepository;

/**
 * 菜单按钮复制服务
 */
final class MenuButtonCopyService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MenuButtonRepository $menuButtonRepository,
    ) {
    }

    /**
     * 从当前菜单复制按钮到版本
     */
    public function copyButtonsFromCurrent(Account $account, MenuVersion $version): void
    {
        $currentButtons = $this->menuButtonRepository->findAllByAccount($account);

        $buttonMap = [];
        foreach ($currentButtons as $button) {
            $versionButton = MenuButtonVersion::createFromMenuButton($button);
            $versionButton->setVersion($version);

            // 保存映射关系，用于处理父子关系
            $buttonMap[$button->getId()] = $versionButton;

            $this->entityManager->persist($versionButton);
        }

        // 设置父子关系
        foreach ($currentButtons as $button) {
            $parent = $button->getParent();
            if (null !== $parent) {
                $versionButton = $buttonMap[$button->getId()];
                $parentVersionButton = $buttonMap[$parent->getId()] ?? null;
                if (null !== $parentVersionButton) {
                    $versionButton->setParent($parentVersionButton);
                }
            }
        }
    }

    /**
     * 从版本复制按钮
     */
    public function copyButtonsFromVersion(MenuVersion $source, MenuVersion $target): void
    {
        foreach ($source->getButtons() as $button) {
            $this->copyButtonVersion($button, $target);
        }
    }

    /**
     * 复制版本按钮
     */
    public function copyButtonVersion(
        MenuButtonVersion $source,
        MenuVersion $targetVersion,
        ?MenuButtonVersion $targetParent = null,
    ): MenuButtonVersion {
        $copy = new MenuButtonVersion();
        $copy->setVersion($targetVersion);
        if (null !== $source->getType()) {
            $copy->setType($source->getType());
        }
        if (null !== $source->getName()) {
            $copy->setName($source->getName());
        }
        $copy->setClickKey($source->getClickKey());
        $copy->setUrl($source->getUrl());
        $copy->setAppId($source->getAppId());
        $copy->setPagePath($source->getPagePath());
        $copy->setMediaId($source->getMediaId());
        $copy->setPosition($source->getPosition());
        $copy->setEnabled($source->isEnabled());
        $copy->setOriginalButtonId($source->getOriginalButtonId());

        if (null !== $targetParent) {
            $copy->setParent($targetParent);
        }

        $this->entityManager->persist($copy);

        // 递归复制子按钮
        foreach ($source->getChildren() as $child) {
            $this->copyButtonVersion($child, $targetVersion, $copy);
        }

        return $copy;
    }
}
