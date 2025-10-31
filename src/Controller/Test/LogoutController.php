<?php

declare(strict_types=1);

namespace WechatOfficialAccountMenuBundle\Controller\Test;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * 测试环境专用的 Logout 控制器
 * 提供最小可用的登出路由，满足 EasyAdmin 模板依赖
 */
final class LogoutController
{
    #[Route(path: '/logout', name: 'app_logout', methods: ['GET', 'POST'])]
    public function __invoke(): Response
    {
        return new Response('Test logout');
    }
}
