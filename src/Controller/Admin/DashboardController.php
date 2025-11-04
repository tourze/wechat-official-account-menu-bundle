<?php

namespace WechatOfficialAccountMenuBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Menu\MenuItemInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use WechatOfficialAccountMenuBundle\Entity\MenuButton;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;

/**
 * 微信公众号菜单管理仪表板控制器
 */
final class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    #[Route(path: '/admin/wechat-menu', name: 'admin_wechat_menu')]
    public function index(): Response
    {
        return $this->redirect($this->adminUrlGenerator->setController(MenuButtonCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('微信公众号菜单管理')
            ->setFaviconPath('favicon.ico')
            ->generateRelativeUrls()
            ->disableUrlSignatures()
            ->setTranslationDomain('messages')
        ;
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        // 返回一个空的用户菜单，完全避免路由生成问题
        return UserMenu::new()
            ->setName($user->getUserIdentifier())
            ->setAvatarUrl(null)
            ->addMenuItems([])
        ;
    }

    /**
     * @return iterable<MenuItemInterface>
     */
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('菜单管理');
        yield MenuItem::linkToCrud('菜单按钮', 'fas fa-list', MenuButton::class)
            ->setController(MenuButtonCrudController::class)
        ;
        yield MenuItem::linkToCrud('菜单版本', 'fas fa-code-branch', MenuVersion::class)
            ->setController(MenuVersionCrudController::class)
        ;
    }
}
