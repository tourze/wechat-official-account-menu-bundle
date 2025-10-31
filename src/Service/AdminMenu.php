<?php

namespace WechatOfficialAccountMenuBundle\Service;

use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use WechatOfficialAccountMenuBundle\Controller\Admin\MenuButtonCrudController;
use WechatOfficialAccountMenuBundle\Controller\Admin\MenuVersionCrudController;
use WechatOfficialAccountMenuBundle\Entity\MenuButton;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;

/**
 * 微信公众号菜单管理后台菜单服务
 */
#[When(env: 'prod')]
#[When(env: 'dev')]
#[Autoconfigure(public: true)]
class AdminMenu implements MenuProviderInterface
{
    public function __construct(
        private readonly ?LinkGeneratorInterface $linkGenerator = null,
        private readonly ?AdminUrlGenerator $adminUrlGenerator = null,
    ) {
    }

    public function __invoke(ItemInterface $item): void
    {
        // 在测试环境或依赖不可用时跳过菜单添加
        if (null === $this->linkGenerator || null === $this->adminUrlGenerator) {
            return;
        }

        // 创建或获取微信管理顶级菜单
        if (null === $item->getChild('微信管理')) {
            $item->addChild('微信管理')
                ->setAttribute('icon', 'fab fa-weixin')
            ;
        }

        $wechatMenu = $item->getChild('微信管理');
        if (null === $wechatMenu) {
            return;
        }

        // 菜单管理子菜单
        $wechatMenu->addChild('菜单管理')
            ->setUri($this->linkGenerator->getCurdListPage(MenuButton::class))
            ->setAttribute('icon', 'fas fa-bars')
            ->setAttribute('data-badge', 'Pro')
        ;

        // 版本管理子菜单
        $wechatMenu->addChild('菜单版本')
            ->setUri($this->linkGenerator->getCurdListPage(MenuVersion::class))
            ->setAttribute('icon', 'fas fa-code-branch')
        ;

        // 菜单树形视图
        $wechatMenu->addChild('树形视图')
            ->setUri($this->adminUrlGenerator
                ->unsetAll()
                ->setController(MenuButtonCrudController::class)
                ->setAction('treeView')
                ->generateUrl()
            )
            ->setAttribute('icon', 'fas fa-sitemap')
        ;

        // 导入导出菜单
        $importExportMenu = $wechatMenu->addChild('导入导出')
            ->setAttribute('icon', 'fas fa-exchange-alt')
        ;

        $importExportMenu->addChild('导入菜单')
            ->setUri($this->adminUrlGenerator
                ->unsetAll()
                ->setController(MenuButtonCrudController::class)
                ->setAction('importMenu')
                ->generateUrl()
            )
            ->setAttribute('icon', 'fas fa-upload')
        ;

        // 版本对比
        $wechatMenu->addChild('版本对比')
            ->setUri($this->adminUrlGenerator
                ->unsetAll()
                ->setController(MenuVersionCrudController::class)
                ->setAction('compareVersions')
                ->generateUrl()
            )
            ->setAttribute('icon', 'fas fa-code-compare')
        ;
    }
}
