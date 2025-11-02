<?php

namespace WechatOfficialAccountMenuBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use WechatOfficialAccountMenuBundle\Entity\MenuButtonVersion;
use WechatOfficialAccountMenuBundle\Enum\MenuType;

/**
 * @extends AbstractCrudController<MenuButtonVersion>
 */
#[AdminCrud(routePath: '/wechat-menu/menu-button-version', routeName: 'wechat_menu_menu_button_version')]
final class MenuButtonVersionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MenuButtonVersion::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('菜单按钮版本')
            ->setEntityLabelInPlural('菜单按钮版本管理')
            ->setDefaultSort(['version.id' => 'DESC', 'position' => 'ASC', 'id' => 'ASC'])
            ->setSearchFields(['id', 'name', 'clickKey', 'url', 'version.version'])
            ->showEntityActionsInlined()
            ->setPageTitle(Crud::PAGE_INDEX, '菜单按钮版本管理')
            ->setPageTitle(Crud::PAGE_NEW, '创建菜单按钮版本')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑菜单按钮版本')
            ->setPageTitle(Crud::PAGE_DETAIL, '菜单按钮版本详情')
        ;
    }

    /**
     * @return iterable<FieldInterface>
     */
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->setMaxLength(9999)
            ->hideOnForm()
        ;

        yield AssociationField::new('version', '所属版本')
            ->setRequired(true)
            ->formatValue(function ($value, MenuButtonVersion $entity) {
                return $entity->getVersion()?->__toString() ?? '';
            })
        ;

        yield AssociationField::new('parent', '上级菜单')
            ->setRequired(false)
            ->formatValue(function ($value, MenuButtonVersion $entity) {
                return $entity->getParent()?->getName() ?? '无';
            })
            ->hideOnIndex()
        ;

        yield TextField::new('name', '菜单名称')
            ->setRequired(true)
            ->setHelp('一级菜单最多4个汉字，二级菜单最多8个汉字')
        ;

        yield ChoiceField::new('type', '菜单类型')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions(['class' => MenuType::class])
            ->formatValue(function ($value) {
                return $value instanceof MenuType ? $value->getLabel() : '';
            })
            ->setRequired(true)
        ;

        yield TextField::new('clickKey', '菜单KEY')
            ->hideOnIndex()
            ->setHelp('click类型必填，用于消息接口推送')
        ;

        yield UrlField::new('url', '跳转URL')
            ->hideOnIndex()
            ->setHelp('view或miniprogram类型需要')
        ;

        yield TextField::new('appId', '小程序AppID')
            ->hideOnIndex()
            ->setHelp('miniprogram类型必填')
        ;

        yield TextareaField::new('pagePath', '小程序页面路径')
            ->hideOnIndex()
            ->setHelp('miniprogram类型可选')
        ;

        yield IntegerField::new('position', '排序')
            ->setHelp('数字越小越靠前')
        ;

        yield BooleanField::new('enabled', '启用');

        yield TextField::new('originalButtonId', '原始按钮ID')
            ->hideOnIndex()
            ->hideOnForm()
        ;

        if (Crud::PAGE_DETAIL === $pageName) {
            yield DateTimeField::new('createTime', '创建时间')->hideOnForm();
            yield DateTimeField::new('updateTime', '更新时间')->hideOnForm();
        }
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('version', '所属版本'))
            ->add(ChoiceFilter::new('type', '菜单类型')->setChoices(array_combine(
                array_map(fn ($case) => $case->getLabel(), MenuType::cases()),
                array_map(fn ($case) => $case->value, MenuType::cases())
            )))
            ->add(BooleanFilter::new('enabled', '是否启用'))
            ->add(TextFilter::new('name', '菜单名称'))
            ->add(EntityFilter::new('parent', '上级菜单'))
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::NEW)
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa fa-edit')->setLabel('编辑');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setIcon('fa fa-trash')->setLabel('删除');
            })
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa fa-eye')->setLabel('详情');
            })
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fa fa-plus')->setLabel('新建版本按钮');
            })
            ->reorder(Crud::PAGE_INDEX, [
                Action::NEW, Action::EDIT, Action::DETAIL, Action::DELETE,
            ])
        ;
    }
}
