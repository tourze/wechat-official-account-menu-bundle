<?php

namespace WechatOfficialAccountMenuBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Entity\MenuButton;
use WechatOfficialAccountMenuBundle\Enum\MenuType;
use WechatOfficialAccountMenuBundle\Exception\MenuValidationException;
use WechatOfficialAccountMenuBundle\Repository\MenuButtonRepository;
use WechatOfficialAccountMenuBundle\Validator\MenuValidator;

class MenuService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MenuButtonRepository $menuButtonRepository,
        private readonly MenuValidator $menuValidator,
        private readonly ValidatorInterface $validator,
    ) {
    }

    /**
     * 创建菜单按钮.
     *
     * @param array<string, mixed> $data
     */
    public function createMenuButton(Account $account, array $data): MenuButton
    {
        $menuButton = new MenuButton();
        $menuButton->setAccount($account);
        $this->updateMenuButtonFromData($menuButton, $data);

        // 设置排序位置
        if (!isset($data['position'])) {
            $position = $this->menuButtonRepository->getNextPosition(
                $account,
                $menuButton->getParent()
            );
            $menuButton->setPosition($position);
        }

        $this->validateMenuButton($menuButton);

        $this->entityManager->persist($menuButton);
        $this->entityManager->flush();

        return $menuButton;
    }

    /**
     * 更新菜单按钮.
     *
     * @param array<string, mixed> $data
     */
    public function updateMenuButton(MenuButton $menuButton, array $data): MenuButton
    {
        $this->updateMenuButtonFromData($menuButton, $data);
        $this->validateMenuButton($menuButton);

        $this->entityManager->flush();

        return $menuButton;
    }

    /**
     * 删除菜单按钮.
     */
    public function deleteMenuButton(MenuButton $menuButton): void
    {
        // 删除所有子菜单
        foreach ($menuButton->getChildren() as $child) {
            $this->entityManager->remove($child);
        }

        $this->entityManager->remove($menuButton);
        $this->entityManager->flush();
    }

    /**
     * 批量更新菜单排序.
     *
     * @param array<string, int> $positions 键为按钮ID，值为新位置
     */
    public function updateMenuPositions(array $positions): void
    {
        $this->menuButtonRepository->updatePositions($positions);
    }

    /**
     * 复制菜单按钮.
     */
    public function copyMenuButton(MenuButton $source, ?MenuButton $targetParent = null): MenuButton
    {
        $copy = new MenuButton();
        $copy->setAccount($source->getAccount());
        if (null !== $source->getType()) {
            $copy->setType($source->getType());
        }
        $copy->setName($source->getName() . ' (副本)');
        $copy->setClickKey($source->getClickKey());
        $copy->setUrl($source->getUrl());
        $copy->setAppId($source->getAppId());
        $copy->setPagePath($source->getPagePath());
        $copy->setEnabled($source->isEnabled());

        if (null !== $targetParent) {
            $copy->setParent($targetParent);
        }

        $position = $this->menuButtonRepository->getNextPosition(
            $source->getAccount(),
            $targetParent
        );
        $copy->setPosition($position);

        $this->entityManager->persist($copy);

        // 递归复制子菜单
        foreach ($source->getChildren() as $child) {
            $this->copyMenuButton($child, $copy);
        }

        $this->entityManager->flush();

        return $copy;
    }

    /**
     * 获取账号的菜单树.
     *
     * @return array<int, MenuButton>
     */
    public function getMenuTree(Account $account, bool $onlyEnabled = true): array
    {
        if ($onlyEnabled) {
            return $this->menuButtonRepository->findRootMenusByAccount($account);
        }

        return $this->menuButtonRepository->findAllByAccount($account);
    }

    /**
     * 验证菜单结构.
     *
     * @return array<int, string>
     */
    public function validateMenuStructure(Account $account): array
    {
        $rootMenus = $this->menuButtonRepository->findRootMenusByAccount($account);

        return $this->menuValidator->validateMenuStructure($rootMenus);
    }

    /**
     * 启用/禁用菜单.
     */
    public function toggleMenuButton(MenuButton $menuButton, bool $enabled): void
    {
        $menuButton->setEnabled($enabled);

        // 如果禁用父菜单，同时禁用所有子菜单
        if (!$enabled) {
            foreach ($menuButton->getChildren() as $child) {
                $child->setEnabled(false);
            }
        }

        $this->entityManager->flush();
    }

    /**
     * 移动菜单到新的父级.
     */
    public function moveMenuButton(MenuButton $menuButton, ?MenuButton $newParent): void
    {
        // 验证不能移动到自己的子节点
        if (null !== $newParent && $this->isDescendant($menuButton, $newParent)) {
            throw new MenuValidationException('不能将菜单移动到其子节点下');
        }

        $menuButton->setParent($newParent);

        // 重新计算位置
        $position = $this->menuButtonRepository->getNextPosition(
            $menuButton->getAccount(),
            $newParent
        );
        $menuButton->setPosition($position);

        $this->validateMenuButton($menuButton);
        $this->entityManager->flush();
    }

    /**
     * 检查是否为子节点.
     */
    private function isDescendant(MenuButton $parent, MenuButton $child): bool
    {
        $current = $child->getParent();
        while (null !== $current) {
            if ($current->getId() === $parent->getId()) {
                return true;
            }
            $current = $current->getParent();
        }

        return false;
    }

    /**
     * 更新菜单按钮数据.
     *
     * @param array<string, mixed> $data
     */
    private function updateMenuButtonFromData(MenuButton $menuButton, array $data): void
    {
        $this->updateBasicData($menuButton, $data);
        $this->updateMenuTypeData($menuButton, $data);
        $this->updateMetaData($menuButton, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateBasicData(MenuButton $menuButton, array $data): void
    {
        if (isset($data['type'])) {
            $type = $data['type'];
            assert($type instanceof MenuType);
            $menuButton->setType($type);
        }
        if (isset($data['name'])) {
            $name = $data['name'];
            assert(is_string($name));
            $menuButton->setName($name);
        }
        if (isset($data['parent'])) {
            /** @var MenuButton|null $parent */
            $parent = $data['parent'];
            $menuButton->setParent($parent);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateMenuTypeData(MenuButton $menuButton, array $data): void
    {
        if (isset($data['clickKey'])) {
            /** @var string|null $clickKey */
            $clickKey = $data['clickKey'];
            $menuButton->setClickKey($clickKey);
        }
        if (isset($data['url'])) {
            /** @var string|null $url */
            $url = $data['url'];
            $menuButton->setUrl($url);
        }
        if (isset($data['appId'])) {
            /** @var string|null $appId */
            $appId = $data['appId'];
            $menuButton->setAppId($appId);
        }
        if (isset($data['pagePath'])) {
            /** @var string|null $pagePath */
            $pagePath = $data['pagePath'];
            $menuButton->setPagePath($pagePath);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateMetaData(MenuButton $menuButton, array $data): void
    {
        if (isset($data['position'])) {
            $position = $data['position'];
            assert(is_int($position));
            $menuButton->setPosition($position);
        }
        if (isset($data['enabled'])) {
            $enabled = $data['enabled'];
            assert(is_bool($enabled));
            $menuButton->setEnabled($enabled);
        }
    }

    /**
     * 获取账号的菜单结构（用于管理界面）.
     *
     * @return array<string, mixed>
     */
    public function getMenuStructureForAccount(Account $account): array
    {
        $menus = $this->getMenuTree($account, false);

        return [
            'account' => [
                'id' => $account->getId(),
                'name' => $account->getName(),
            ],
            'menus' => $this->convertMenusToArray($menus),
            'total' => count($menus),
        ];
    }

    /**
     * 获取所有有菜单的账号.
     *
     * @return array<int, Account>
     */
    public function getAllAccountsWithMenus(): array
    {
        return $this->menuButtonRepository->findAccountsWithMenus();
    }

    /**
     * 将菜单数组转换为数组格式.
     *
     * @param array<int, MenuButton> $menus
     *
     * @return array<int, array<string, mixed>>
     */
    private function convertMenusToArray(array $menus): array
    {
        $result = [];
        foreach ($menus as $menu) {
            $menuData = [
                'id' => $menu->getId(),
                'name' => $menu->getName(),
                'type' => $menu->getType()?->value,
                'position' => $menu->getPosition(),
                'enabled' => $menu->isEnabled(),
                'children' => [],
            ];

            if ($menu->getChildren()->count() > 0) {
                $menuData['children'] = $this->convertMenusToArray($menu->getChildren()->toArray());
            }

            $result[] = $menuData;
        }

        return $result;
    }

    /**
     * 验证菜单按钮.
     */
    private function validateMenuButton(MenuButton $menuButton): void
    {
        // 使用Symfony验证器
        $errors = $this->validator->validate($menuButton);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }
            throw new MenuValidationException(implode(', ', $messages));
        }

        // 自定义业务验证
        $menuButton->ensureSameAccount();

        // 验证菜单层级和数量限制
        $this->menuValidator->validateMenuButton($menuButton);
    }
}
