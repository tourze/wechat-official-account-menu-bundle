<?php

namespace WechatOfficialAccountMenuBundle\Validator;

use WechatOfficialAccountMenuBundle\Entity\MenuButton;
use WechatOfficialAccountMenuBundle\Entity\MenuButtonVersion;
use WechatOfficialAccountMenuBundle\Enum\MenuType;
use WechatOfficialAccountMenuBundle\Exception\MenuValidationException;

class MenuValidator
{
    private const MAX_ROOT_BUTTONS = 3;
    private const MAX_SUB_BUTTONS = 5;
    private const MAX_NAME_LENGTH = 60;
    private const MAX_KEY_LENGTH = 128;
    private const MAX_URL_LENGTH = 1024;

    /**
     * 验证菜单按钮.
     */
    public function validateMenuButton(MenuButton $button): void
    {
        $this->validateButtonCommon($button->getName(), $button->getType(), $button->getParent());
        $this->validateButtonFields($button);
        $this->validateButtonHierarchy($button);
    }

    /**
     * 验证版本菜单按钮.
     */
    public function validateMenuButtonVersion(MenuButtonVersion $button): void
    {
        $this->validateButtonCommon($button->getName(), $button->getType(), $button->getParent());
        $this->validateVersionButtonFields($button);
    }

    /**
     * 验证菜单结构.
     *
     * @param array<int, MenuButton> $rootButtons
     *
     * @return array<int, string>
     */
    public function validateMenuStructure(array $rootButtons): array
    {
        $errors = [];

        // 检查一级菜单数量
        if (count($rootButtons) > self::MAX_ROOT_BUTTONS) {
            $errors[] = sprintf('一级菜单最多%d个，当前有%d个', self::MAX_ROOT_BUTTONS, count($rootButtons));
        }

        // 检查每个一级菜单
        foreach ($rootButtons as $rootButton) {
            // 检查子菜单数量
            $subCount = $rootButton->getChildren()->count();
            if ($subCount > self::MAX_SUB_BUTTONS) {
                $errors[] = sprintf(
                    '菜单"%s"的子菜单最多%d个，当前有%d个',
                    $rootButton->getName(),
                    self::MAX_SUB_BUTTONS,
                    $subCount
                );
            }

            // 有子菜单的一级菜单不能有动作
            if ($subCount > 0 && MenuType::NONE !== $rootButton->getType()) {
                $errors[] = sprintf(
                    '菜单"%s"有子菜单时不能设置动作类型',
                    $rootButton->getName()
                );
            }
        }

        return $errors;
    }

    /**
     * 验证按钮通用规则.
     * @param mixed $parent
     */
    private function validateButtonCommon(?string $name, ?MenuType $type, $parent): void
    {
        // 验证名称
        if (null === $name || '' === $name) {
            throw new MenuValidationException('菜单名称不能为空');
        }

        if (mb_strlen($name) > self::MAX_NAME_LENGTH) {
            throw new MenuValidationException(sprintf('菜单名称最多%d个字符', self::MAX_NAME_LENGTH));
        }

        // 验证类型
        if (null === $type && null === $parent) {
            throw new MenuValidationException('一级菜单必须设置类型');
        }
    }

    /**
     * 验证按钮字段.
     */
    private function validateButtonFields(MenuButton $button): void
    {
        $type = $button->getType();

        if (null === $type) {
            return;
        }

        $this->validateKeyBasedButton($button, $type);
        $this->validateViewButton($button, $type);
        $this->validateMiniProgramButton($button, $type);
    }

    private function validateKeyBasedButton(MenuButton $button, MenuType $type): void
    {
        $keyBasedTypes = [
            MenuType::CLICK,
            MenuType::SCAN_CODE_PUSH,
            MenuType::SCAN_CODE_WAIT_MSG,
            MenuType::PIC_SYS_PHOTO,
            MenuType::PIC_PHOTO_ALBUM,
            MenuType::PIC_WEIXIN,
            MenuType::LOCATION_SELECT,
        ];

        if (!in_array($type, $keyBasedTypes, true)) {
            return;
        }

        $clickKey = $button->getClickKey();
        if (null === $clickKey || '' === $clickKey) {
            throw new MenuValidationException(sprintf('类型为"%s"的菜单必须设置Key值', $type->getLabel()));
        }

        $clickKey = $button->getClickKey();
        if (null !== $clickKey && strlen($clickKey) > self::MAX_KEY_LENGTH) {
            throw new MenuValidationException(sprintf('Key值最多%d个字符', self::MAX_KEY_LENGTH));
        }
    }

    private function validateViewButton(MenuButton $button, MenuType $type): void
    {
        if (MenuType::VIEW !== $type) {
            return;
        }

        $url = $button->getUrl();
        if (null === $url || '' === $url) {
            throw new MenuValidationException('跳转URL类型的菜单必须设置URL');
        }

        $url = $button->getUrl();
        if (null !== $url && strlen($url) > self::MAX_URL_LENGTH) {
            throw new MenuValidationException(sprintf('URL最多%d个字符', self::MAX_URL_LENGTH));
        }

        if (false === filter_var($button->getUrl(), FILTER_VALIDATE_URL)) {
            throw new MenuValidationException('URL格式不正确');
        }
    }

    private function validateMiniProgramButton(MenuButton $button, MenuType $type): void
    {
        if (MenuType::MINI_PROGRAM !== $type) {
            return;
        }

        $url = $button->getUrl();
        if (null === $url || '' === $url) {
            throw new MenuValidationException('小程序类型的菜单必须设置备用网页URL');
        }

        $appId = $button->getAppId();
        if (null === $appId || '' === $appId) {
            throw new MenuValidationException('小程序类型的菜单必须设置AppID');
        }

        $pagePath = $button->getPagePath();
        if (null === $pagePath || '' === $pagePath) {
            throw new MenuValidationException('小程序类型的菜单必须设置页面路径');
        }
    }

    /**
     * 验证版本按钮字段.
     */
    private function validateVersionButtonFields(MenuButtonVersion $button): void
    {
        $type = $button->getType();

        if (null === $type) {
            return;
        }

        $this->validateVersionKeyBasedButton($button, $type);
        $this->validateVersionViewButton($button, $type);
        $this->validateVersionMiniProgramButton($button, $type);
    }

    private function validateVersionKeyBasedButton(MenuButtonVersion $button, MenuType $type): void
    {
        $keyBasedTypes = [
            MenuType::CLICK,
            MenuType::SCAN_CODE_PUSH,
            MenuType::SCAN_CODE_WAIT_MSG,
            MenuType::PIC_SYS_PHOTO,
            MenuType::PIC_PHOTO_ALBUM,
            MenuType::PIC_WEIXIN,
            MenuType::LOCATION_SELECT,
        ];

        if (in_array($type, $keyBasedTypes, true)) {
            $clickKey = $button->getClickKey();
            if (null === $clickKey || '' === $clickKey) {
                throw new MenuValidationException(sprintf('类型为"%s"的菜单必须设置Key值', $type->getLabel()));
            }
        }
    }

    private function validateVersionViewButton(MenuButtonVersion $button, MenuType $type): void
    {
        if (MenuType::VIEW === $type) {
            $url = $button->getUrl();
            if (null === $url || '' === $url) {
                throw new MenuValidationException('跳转URL类型的菜单必须设置URL');
            }
        }
    }

    private function validateVersionMiniProgramButton(MenuButtonVersion $button, MenuType $type): void
    {
        if (MenuType::MINI_PROGRAM === $type) {
            $url = $button->getUrl();
            $appId = $button->getAppId();
            $pagePath = $button->getPagePath();

            if (null === $url || '' === $url || null === $appId || '' === $appId || null === $pagePath || '' === $pagePath) {
                throw new MenuValidationException('小程序类型的菜单必须设置完整信息');
            }
        }
    }

    /**
     * 验证按钮层级.
     */
    private function validateButtonHierarchy(MenuButton $button): void
    {
        // 验证层级深度
        $depth = $this->getButtonDepth($button);
        if ($depth > 2) {
            throw new MenuValidationException('菜单最多支持二级');
        }

        // 如果是父菜单，检查是否设置了动作
        if ($button->getChildren()->count() > 0 && MenuType::NONE !== $button->getType()) {
            throw new MenuValidationException('有子菜单的菜单不能设置动作类型');
        }

        // 检查同级菜单数量
        $parent = $button->getParent();
        if (null !== $parent) {
            $siblingCount = $parent->getChildren()->count();
            if ($siblingCount > self::MAX_SUB_BUTTONS) {
                throw new MenuValidationException(sprintf('二级菜单最多%d个', self::MAX_SUB_BUTTONS));
            }
        }
    }

    /**
     * 获取按钮深度.
     */
    private function getButtonDepth(MenuButton $button): int
    {
        $depth = 1;
        $current = $button;

        $parent = $current->getParent();
        while (null !== $parent) {
            ++$depth;
            $current = $parent;
            $parent = $current->getParent();
        }

        return $depth;
    }
}
