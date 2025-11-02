<?php

namespace WechatOfficialAccountMenuBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminAction;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\HttpFoundation\Response;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;
use WechatOfficialAccountMenuBundle\Enum\MenuVersionStatus;
use WechatOfficialAccountMenuBundle\Service\MenuVersionService;

/**
 * @extends AbstractCrudController<MenuVersion>
 */
#[AdminCrud(routePath: '/wechat-menu/menu-version', routeName: 'wechat_menu_menu_version')]
final class MenuVersionCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly MenuVersionService $versionService,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return MenuVersion::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('菜单版本')
            ->setEntityLabelInPlural('版本管理')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['id', 'version', 'description', 'account.name'])
            ->showEntityActionsInlined()
            ->setPageTitle(Crud::PAGE_INDEX, '菜单版本管理')
            ->setPageTitle(Crud::PAGE_NEW, '创建菜单版本')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑菜单版本')
            ->setPageTitle(Crud::PAGE_DETAIL, '菜单版本详情')
            ->overrideTemplate('crud/detail', '@WechatOfficialAccountMenu/admin/version/detail.html.twig')
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

        yield AssociationField::new('account', '公众号')
            ->setRequired(true)
            ->autocomplete()
            ->formatValue(function ($value, MenuVersion $entity) {
                return $entity->getAccount()->getName();
            })
        ;

        yield TextField::new('version', '版本号')
            ->setRequired(true)
            ->setHelp('为版本起一个易于识别的名称')
        ;

        yield TextareaField::new('description', '版本描述')
            ->setHelp('描述此版本的主要变更内容')
            ->hideOnIndex()
        ;

        yield ChoiceField::new('status', '状态')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions(['class' => MenuVersionStatus::class])
            ->formatValue(function ($value) {
                return $value instanceof MenuVersionStatus ? $value->getLabel() : '';
            })
            ->renderAsBadges([
                MenuVersionStatus::DRAFT->value => 'secondary',
                MenuVersionStatus::PUBLISHED->value => 'success',
                MenuVersionStatus::ARCHIVED->value => 'dark',
            ])
            ->setDisabled(Crud::PAGE_EDIT === $pageName)
        ;

        yield DateTimeField::new('publishedAt', '发布时间')
            ->hideOnForm()
            ->formatValue(function ($value, MenuVersion $entity) {
                return $entity->getPublishedAt()?->format('Y-m-d H:i:s') ?? '-';
            })
        ;

        yield IntegerField::new('buttonsCount', '菜单数量')
            ->hideOnForm()
            ->formatValue(function ($value, MenuVersion $entity) {
                return $entity->getButtons()->count();
            })
        ;

        if (Crud::PAGE_DETAIL === $pageName) {
            yield CodeEditorField::new('menuSnapshotJson', '菜单快照')
                ->hideOnForm()
                ->setLanguage('javascript')
                ->formatValue(function ($value, MenuVersion $entity) {
                    return json_encode($entity->getMenuSnapshot(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                })
            ;
        }

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
        ;

        yield TextField::new('createdBy', '创建人')
            ->hideOnForm()
            ->hideOnIndex()
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('account', '公众号'))
            ->add(ChoiceFilter::new('status', '状态')->setChoices(
                array_combine(
                    array_map(fn ($s) => $s->getLabel(), MenuVersionStatus::cases()),
                    array_map(fn ($s) => $s->value, MenuVersionStatus::cases())
                )
            ))
            ->add(DateTimeFilter::new('publishedAt', '发布时间'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $createFromCurrent = Action::new('createFromCurrent', '基于当前菜单创建', 'fa fa-copy')
            ->linkToCrudAction('createFromCurrent')
            ->setCssClass('btn btn-info')
            ->createAsGlobalAction()
        ;

        $cloneVersion = Action::new('cloneVersion', '克隆版本', 'fa fa-clone')
            ->linkToCrudAction('cloneVersion')
            ->displayIf(function (MenuVersion $entity) {
                return MenuVersionStatus::ARCHIVED !== $entity->getStatus();
            })
        ;

        $publishVersion = Action::new('publishVersion', '发布到微信', 'fa fa-cloud-upload')
            ->linkToCrudAction('publishVersion')
            ->setCssClass('btn btn-success')
            ->displayIf(function (MenuVersion $entity) {
                return MenuVersionStatus::DRAFT === $entity->getStatus();
            })
        ;

        $archiveVersion = Action::new('archiveVersion', '归档', 'fa fa-archive')
            ->linkToCrudAction('archiveVersion')
            ->displayIf(function (MenuVersion $entity) {
                return MenuVersionStatus::PUBLISHED === $entity->getStatus();
            })
        ;

        $restoreVersion = Action::new('restoreVersion', '恢复', 'fa fa-undo')
            ->linkToCrudAction('restoreVersion')
            ->displayIf(function (MenuVersion $entity) {
                return MenuVersionStatus::ARCHIVED === $entity->getStatus();
            })
        ;

        $editMenus = Action::new('editMenus', '编辑菜单', 'fa fa-sitemap')
            ->linkToCrudAction('editMenus')
            ->setCssClass('btn btn-primary')
            ->displayIf(function (MenuVersion $entity) {
                return MenuVersionStatus::DRAFT === $entity->getStatus();
            })
        ;

        $compareVersions = Action::new('compare', '版本对比', 'fa fa-exchange')
            ->linkToCrudAction('compareVersions')
            ->createAsGlobalAction()
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, $createFromCurrent)
            ->add(Crud::PAGE_INDEX, $compareVersions)
            ->add(Crud::PAGE_INDEX, $cloneVersion)
            ->add(Crud::PAGE_INDEX, $publishVersion)
            ->add(Crud::PAGE_INDEX, $archiveVersion)
            ->add(Crud::PAGE_INDEX, $restoreVersion)
            ->add(Crud::PAGE_INDEX, $editMenus)
            ->add(Crud::PAGE_DETAIL, $editMenus)
            ->add(Crud::PAGE_DETAIL, $publishVersion)
            ->add(Crud::PAGE_DETAIL, $cloneVersion)
        ;
    }

    /**
     * 基于当前菜单创建版本
     */
    #[AdminAction(routePath: 'createFromCurrent', routeName: 'createFromCurrent')]
    public function createFromCurrent(AdminContext $context): Response
    {
        return $this->render('@WechatOfficialAccountMenu/admin/version/create_from_current.html.twig');
    }

    /**
     * 克隆版本
     */
    #[AdminAction(routePath: '{entityId}/clone', routeName: 'cloneVersion')]
    public function cloneVersion(AdminContext $context): Response
    {
        $version = $context->getEntity()->getInstance();
        /** @var MenuVersion $version */

        try {
            $newVersion = $this->versionService->createVersion(
                $version->getAccount(),
                '克隆自版本 ' . $version->getVersion(),
                $version
            );
            $this->addFlash('success', sprintf('版本"%s"克隆成功！', $newVersion));

            return $this->redirect($this->adminUrlGenerator
                ->setController(self::class)
                ->setAction('editMenus')
                ->setEntityId($newVersion->getId())
                ->generateUrl()
            );
        } catch (\Exception $e) {
            $this->addFlash('danger', '克隆失败：' . $e->getMessage());

            return $this->redirect($this->adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
            );
        }
    }

    /**
     * 发布版本到微信
     */
    #[AdminAction(routePath: '{entityId}/publish', routeName: 'publishVersion')]
    public function publishVersion(AdminContext $context): Response
    {
        $version = $context->getEntity()->getInstance();
        /** @var MenuVersion $version */

        try {
            $this->versionService->publishVersion($version);
            $this->addFlash('success', sprintf('版本"%s"已成功发布到微信！', $version));
        } catch (\Exception $e) {
            $this->addFlash('danger', '发布失败：' . $e->getMessage());
        }

        return $this->redirect($this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl()
        );
    }

    /**
     * 归档版本
     */
    #[AdminAction(routePath: '{entityId}/archive', routeName: 'archiveVersion')]
    public function archiveVersion(AdminContext $context): Response
    {
        $version = $context->getEntity()->getInstance();
        /** @var MenuVersion $version */

        try {
            $this->versionService->archiveVersion($version);
            $this->addFlash('success', sprintf('版本"%s"已归档！', $version));
        } catch (\Exception $e) {
            $this->addFlash('danger', '归档失败：' . $e->getMessage());
        }

        return $this->redirect($this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl()
        );
    }

    /**
     * 恢复版本
     */
    #[AdminAction(routePath: '{entityId}/restore', routeName: 'restoreVersion')]
    public function restoreVersion(AdminContext $context): Response
    {
        $version = $context->getEntity()->getInstance();
        /** @var MenuVersion $version */

        try {
            $newVersion = $this->versionService->rollbackToVersion($version);
            $this->addFlash('success', sprintf('版本"%s"已恢复为草稿！', $version));
        } catch (\Exception $e) {
            $this->addFlash('danger', '恢复失败：' . $e->getMessage());
        }

        return $this->redirect($this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl()
        );
    }

    /**
     * 编辑版本菜单
     */
    #[AdminAction(routePath: '{entityId}/edit-menus', routeName: 'editMenus')]
    public function editMenus(AdminContext $context): Response
    {
        $version = $context->getEntity()->getInstance();
        /** @var MenuVersion $version */

        if (MenuVersionStatus::DRAFT !== $version->getStatus()) {
            $this->addFlash('warning', '只有草稿状态的版本才能编辑菜单！');

            return $this->redirect($this->adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
            );
        }

        return $this->render('@WechatOfficialAccountMenu/admin/version/edit_menus.html.twig', [
            'version' => $version,
        ]);
    }

    /**
     * 版本对比
     */
    #[AdminAction(routePath: 'compare', routeName: 'compareVersions')]
    public function compareVersions(AdminContext $context): Response
    {
        return $this->render('@WechatOfficialAccountMenu/admin/version/compare.html.twig');
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var MenuVersion $entityInstance */
        parent::persistEntity($entityManager, $entityInstance);
        $this->addFlash('success', sprintf('版本"%s"创建成功！', $entityInstance));
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var MenuVersion $entityInstance */

        if (MenuVersionStatus::PUBLISHED === $entityInstance->getStatus()) {
            $this->addFlash('danger', '不能删除已发布的版本！');

            return;
        }

        $name = (string) $entityInstance;
        parent::deleteEntity($entityManager, $entityInstance);
        $this->addFlash('success', sprintf('版本"%s"删除成功！', $name));
    }
}
