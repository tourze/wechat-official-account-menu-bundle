<?php

namespace WechatOfficialAccountMenuBundle\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * 菜单树形结构编辑字段
 */
final class MenuTreeField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName, ?string $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setTemplatePath('@WechatOfficialAccountMenu/admin/field/menu_tree.html.twig')
            ->setFormType(HiddenType::class)
            ->addCssClass('menu-tree-field')
            ->addJsFiles('bundles/wechatofficialaccountmenu/admin/menu-tree-editor.js')
            ->addJsFiles('https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js')
            ->addCssFiles('bundles/wechatofficialaccountmenu/admin/menu-tree-editor.css')
            ->setDefaultColumns('col-md-12')
        ;
    }

    /**
     * @param array<string, mixed> $menuData
     */
    public function setMenuData(array $menuData): void
    {
        $this->setCustomOption('menuData', $menuData);
    }

    public function setAccountId(string $accountId): void
    {
        $this->setCustomOption('accountId', $accountId);
    }

    public function setVersionId(?string $versionId): void
    {
        $this->setCustomOption('versionId', $versionId);
    }

    public function enableDragAndDrop(bool $enable = true): self
    {
        $this->setCustomOption('dragAndDrop', $enable);

        return $this;
    }

    public function setMaxRootMenus(int $max = 3): void
    {
        $this->setCustomOption('maxRootMenus', $max);
    }

    public function setMaxSubMenus(int $max = 5): void
    {
        $this->setCustomOption('maxSubMenus', $max);
    }

    public function setReadOnly(bool $readOnly = true): void
    {
        $this->setCustomOption('readOnly', $readOnly);
    }

    public function showPreview(bool $show = true): self
    {
        $this->setCustomOption('showPreview', $show);

        return $this;
    }

    /**
     * @param array<string, string> $endpoints
     */
    public function setApiEndpoints(array $endpoints): void
    {
        $defaultEndpoints = [
            'getMenus' => '/admin/menu/version/{versionId}/menus',
            'saveMenu' => '/admin/menu/version/{versionId}/menu/{menuId}',
            'createMenu' => '/admin/menu/version/{versionId}/menu',
            'deleteMenu' => '/admin/menu/version/{versionId}/menu/{menuId}',
            'updatePositions' => '/admin/menu/version/{versionId}/positions',
        ];

        $this->setCustomOption('apiEndpoints', array_merge($defaultEndpoints, $endpoints));
    }
}
