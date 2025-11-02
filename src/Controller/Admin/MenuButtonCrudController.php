<?php

namespace WechatOfficialAccountMenuBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminAction;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
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
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Entity\MenuButton;
use WechatOfficialAccountMenuBundle\Enum\MenuType;
use WechatOfficialAccountMenuBundle\Exception\MenuValidationException;
use WechatOfficialAccountMenuBundle\Repository\MenuButtonRepository;
use WechatOfficialAccountMenuBundle\Service\MenuService;
use WechatOfficialAccountMenuBundle\Service\WechatMenuApiService;

/**
 * @extends AbstractCrudController<MenuButton>
 */
#[AdminCrud(routePath: '/wechat-menu/menu-button', routeName: 'wechat_menu_menu_button')]
final class MenuButtonCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly MenuService $menuService,
        private readonly WechatMenuApiService $wechatMenuApiService,
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly MenuButtonRepository $menuButtonRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return MenuButton::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('菜单按钮')
            ->setEntityLabelInPlural('菜单管理')
            ->setDefaultSort(['account.id' => 'ASC', 'position' => 'ASC', 'id' => 'ASC'])
            ->setSearchFields(['name', 'clickKey', 'url', 'account.name'])
            ->showEntityActionsInlined()
            ->setPageTitle(Crud::PAGE_INDEX, '微信公众号菜单管理')
            ->setPageTitle(Crud::PAGE_NEW, '创建菜单按钮')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑菜单按钮')
            ->setPageTitle(Crud::PAGE_DETAIL, '菜单按钮详情')
            ->overrideTemplate('crud/index', '@WechatOfficialAccountMenu/admin/menu/index.html.twig')
            ->overrideTemplate('crud/new', '@WechatOfficialAccountMenu/admin/menu/form.html.twig')
            ->overrideTemplate('crud/edit', '@WechatOfficialAccountMenu/admin/menu/form.html.twig')
        ;
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addJsFile('bundles/wechatofficialaccountmenu/admin/menu-editor.js')
            ->addCssFile('bundles/wechatofficialaccountmenu/admin/menu-editor.css')
        ;
    }

    /**
     * @return iterable<FieldInterface>
     */
    public function configureFields(string $pageName): iterable
    {
        // 在列表页显示树形结构
        if (Crud::PAGE_INDEX === $pageName) {
            yield IdField::new('id', 'ID')->hideOnForm()->setMaxLength(9999);
            yield AssociationField::new('account', '公众号')
                ->formatValue(function ($value, MenuButton $entity) {
                    return $entity->getAccount()->getName();
                })
            ;
            yield TextField::new('displayName', '菜单名称')
                ->formatValue(function ($value, MenuButton $entity) {
                    $level = 0;
                    $parent = $entity->getParent();
                    while (null !== $parent) {
                        ++$level;
                        $parent = $parent->getParent();
                    }
                    $prefix = str_repeat('├─ ', $level);

                    return $prefix . $entity->getName();
                })
            ;
            yield ChoiceField::new('type', '类型')
                ->setFormType(EnumType::class)
                ->setFormTypeOptions(['class' => MenuType::class])
                ->formatValue(function ($value) {
                    return $value instanceof MenuType ? $value->getLabel() : '';
                })
            ;
            yield IntegerField::new('position', '排序');
            yield BooleanField::new('enabled', '启用');
            yield DateTimeField::new('updateTime', '更新时间');

            return;
        }

        // 表单页字段
        yield AssociationField::new('account', '公众号')
            ->setRequired(true)
            ->setHelp('选择菜单所属的公众号')
        ;

        yield AssociationField::new('parent', '上级菜单')
            ->setRequired(false)
            ->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                return $queryBuilder
                    ->andWhere('entity.parent IS NULL')
                    ->orderBy('entity.position', 'ASC')
                ;
            })
            ->setHelp('留空表示创建一级菜单')
        ;

        yield TextField::new('name', '菜单名称')
            ->setRequired(true)
            ->setHelp('一级菜单最多4个汉字，二级菜单最多8个汉字')
        ;

        yield ChoiceField::new('type', '菜单类型')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions(['class' => MenuType::class])
            ->setRequired(true)
            ->setHelp('选择菜单的响应类型')
            ->formatValue(function ($value) {
                return $value instanceof MenuType ? $value->getLabel() : '';
            })
        ;

        yield TextField::new('clickKey', '菜单KEY')
            ->setRequired(false)
            ->setHelp('click类型必填，用于消息接口推送')
            ->setFormTypeOption('attr', ['data-show-when-type' => 'click'])
        ;

        yield UrlField::new('url', '跳转URL')
            ->setRequired(false)
            ->setHelp('view类型必填，用户点击菜单可打开链接')
            ->setFormTypeOption('attr', ['data-show-when-type' => 'view'])
        ;

        yield TextField::new('appId', '小程序AppID')
            ->setRequired(false)
            ->setHelp('miniprogram类型必填')
            ->setFormTypeOption('attr', ['data-show-when-type' => 'miniprogram'])
        ;

        yield TextareaField::new('pagePath', '小程序页面路径')
            ->setRequired(false)
            ->setHelp('miniprogram类型必填，小程序的页面路径')
            ->setFormTypeOption('attr', ['data-show-when-type' => 'miniprogram'])
        ;

        yield IntegerField::new('position', '排序位置')
            ->setHelp('数字越小越靠前')
            ->setFormTypeOption('attr', ['min' => 0])
        ;

        yield BooleanField::new('enabled', '是否启用')
            ->setHelp('禁用的菜单不会发布到微信')
        ;

        if (Crud::PAGE_DETAIL === $pageName) {
            yield IdField::new('id')->setMaxLength(9999);
            yield TextField::new('createdBy', '创建人')->hideOnForm();
            yield TextField::new('updatedBy', '更新人')->hideOnForm();
            yield DateTimeField::new('createTime', '创建时间')->hideOnForm();
            yield DateTimeField::new('updateTime', '更新时间')->hideOnForm();
        }
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('account', '公众号'))
            ->add(ChoiceFilter::new('type', '菜单类型')->setChoices(array_combine(
                array_map(fn ($case) => $case->getLabel(), MenuType::cases()),
                array_map(fn ($case) => $case->value, MenuType::cases())
            )))
            ->add(BooleanFilter::new('enabled', '是否启用'))
            ->add(TextFilter::new('name', '菜单名称'))
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $syncToWechat = Action::new('syncToWechat', '同步到微信', 'fa fa-sync')
            ->linkToCrudAction('syncToWechat')
            ->setCssClass('btn btn-success')
            ->displayIf(function (MenuButton $entity) {
                return true; // 总是显示同步按钮
            })
        ;

        $treeView = Action::new('treeView', '树形视图', 'fa fa-sitemap')
            ->linkToCrudAction('treeView')
            ->createAsGlobalAction()
        ;

        $importMenu = Action::new('importMenu', '导入菜单', 'fa fa-upload')
            ->linkToCrudAction('importMenu')
            ->createAsGlobalAction()
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, $syncToWechat)
            ->add(Crud::PAGE_INDEX, $treeView)
            ->add(Crud::PAGE_INDEX, $importMenu)
        ;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        // 按照树形结构排序
        $queryBuilder
            ->leftJoin('entity.parent', 'parent')
            ->orderBy('entity.account', 'ASC')
            ->addOrderBy('CASE WHEN entity.parent IS NULL THEN entity.position ELSE parent.position END', 'ASC')
            ->addOrderBy('CASE WHEN entity.parent IS NULL THEN 0 ELSE 1 END', 'ASC')
            ->addOrderBy('entity.position', 'ASC')
        ;

        return $queryBuilder;
    }

    /**
     * 同步菜单到微信
     */
    #[AdminAction(routePath: '{entityId}/sync', routeName: 'syncToWechat')]
    public function syncToWechat(AdminContext $context): Response
    {
        $entity = $context->getEntity()->getInstance();
        /** @var MenuButton $entity */

        try {
            $account = $entity->getAccount();
            $menuStructure = $this->menuService->getMenuStructureForAccount($account);

            $this->wechatMenuApiService->createMenu($account, $menuStructure);

            $this->addFlash('success', '菜单已成功同步到微信！');
        } catch (\Exception $e) {
            $this->addFlash('danger', '同步失败：' . $e->getMessage());
        }

        return $this->redirect($this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl()
        );
    }

    /**
     * 树形视图
     */
    #[AdminAction(routePath: 'tree', routeName: 'treeView')]
    public function treeView(AdminContext $context): Response
    {
        return $this->render('@WechatOfficialAccountMenu/admin/menu/tree_view.html.twig', [
            'accounts' => $this->menuService->getAllAccountsWithMenus(),
        ]);
    }

    /**
     * 导入菜单
     */
    #[AdminAction(routePath: 'import', routeName: 'importMenu')]
    public function importMenu(AdminContext $context): Response
    {
        return $this->render('@WechatOfficialAccountMenu/admin/menu/import.html.twig');
    }

    /**
     * AJAX接口：更新菜单排序
     */
    public function updatePositions(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $positions = $request->request->all('positions');

        try {
            foreach ($positions as $id => $position) {
                $menu = $this->menuButtonRepository->find($id);
                if (null !== $menu && (is_int($position) || is_string($position))) {
                    $menu->setPosition((int) $position);
                }
            }

            $entityManager->flush();

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX接口：获取账号菜单结构
     */
    public function getAccountMenuStructure(Account $account): JsonResponse
    {
        try {
            $structure = $this->menuService->getMenuStructureForAccount($account);

            return new JsonResponse($structure);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var MenuButton $entityInstance */

        // 验证菜单结构
        $this->validateMenuStructure($entityInstance);

        parent::persistEntity($entityManager, $entityInstance);
        $this->addFlash('success', sprintf('菜单"%s"创建成功！', $entityInstance->getName()));
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var MenuButton $entityInstance */

        // 验证菜单结构
        $this->validateMenuStructure($entityInstance);

        parent::updateEntity($entityManager, $entityInstance);
        $this->addFlash('success', sprintf('菜单"%s"更新成功！', $entityInstance->getName()));
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var MenuButton $entityInstance */
        $name = $entityInstance->getName();

        // 如果有子菜单，需要先删除子菜单
        if (!$entityInstance->getChildren()->isEmpty()) {
            $this->addFlash('danger', '请先删除子菜单！');

            return;
        }

        parent::deleteEntity($entityManager, $entityInstance);
        $this->addFlash('success', sprintf('菜单"%s"删除成功！', $name));
    }

    private function validateMenuStructure(MenuButton $menuButton): void
    {
        $account = $menuButton->getAccount();

        // 如果是一级菜单
        if (null === $menuButton->getParent()) {
            $rootMenus = $this->menuButtonRepository->findBy(['account' => $account, 'parent' => null]);

            if (count($rootMenus) >= 3 && !in_array($menuButton, $rootMenus, true)) {
                throw new MenuValidationException('一级菜单最多只能有3个！');
            }
        } else {
            // 如果是二级菜单
            $parent = $menuButton->getParent();
            $subMenus = $parent->getChildren();

            if (count($subMenus) >= 5 && !$subMenus->contains($menuButton)) {
                throw new MenuValidationException('每个一级菜单最多只能有5个子菜单！');
            }
        }
    }
}
