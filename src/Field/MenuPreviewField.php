<?php

namespace WechatOfficialAccountMenuBundle\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;

/**
 * 菜单预览字段 - 模拟微信公众号菜单显示效果
 */
final class MenuPreviewField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName, ?string $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setTemplatePath('@WechatOfficialAccountMenu/admin/field/menu_preview.html.twig')
            ->setFormTypeOption('mapped', false)
            ->addCssClass('menu-preview-field')
            ->addCssFiles('bundles/wechatofficialaccountmenu/admin/menu-preview.css')
            ->setDefaultColumns('col-md-4')
        ;
    }

    /**
     * @param array<string, mixed> $menuData
     * @phpstan-ignore symplify.noReturnSetterMethod
     */
    public function setMenuData(array $menuData): self
    {
        $this->setCustomOption('menuData', $menuData);

        return $this;
    }

    /** @phpstan-ignore symplify.noReturnSetterMethod */
    public function setAccountName(string $accountName): self
    {
        $this->setCustomOption('accountName', $accountName);

        return $this;
    }

    /** @phpstan-ignore symplify.noReturnSetterMethod */
    public function setShowMobileFrame(bool $show = true): self
    {
        $this->setCustomOption('showMobileFrame', $show);

        return $this;
    }

    /** @phpstan-ignore symplify.noReturnSetterMethod */
    public function setInteractive(bool $interactive = false): self
    {
        $this->setCustomOption('interactive', $interactive);

        return $this;
    }

    /** @phpstan-ignore symplify.noReturnSetterMethod */
    public function setScale(float $scale = 1.0): self
    {
        $this->setCustomOption('scale', $scale);

        return $this;
    }

    /** @phpstan-ignore symplify.noReturnSetterMethod */
    public function setHighlightMenuId(?string $menuId): self
    {
        $this->setCustomOption('highlightMenuId', $menuId);

        return $this;
    }

    /** @phpstan-ignore symplify.noReturnSetterMethod */
    public function setShowMenuInfo(bool $show = true): self
    {
        $this->setCustomOption('showMenuInfo', $show);

        return $this;
    }
}
