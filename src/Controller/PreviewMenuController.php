<?php

namespace WechatOfficialAccountMenuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Repository\AccountRepository;
use WechatOfficialAccountMenuBundle\DTO\MenuPreviewDTO;
use WechatOfficialAccountMenuBundle\Service\MenuService;

final class PreviewMenuController extends AbstractController
{
    public function __construct(
        private readonly MenuService $menuService,
        private readonly AccountRepository $accountRepository,
    ) {
    }

    /**
     * 预览菜单.
     */
    #[Route(path: '/api/wechat/menu/preview/{accountId}', name: 'wechat_menu_preview', methods: ['GET'])]
    public function __invoke(string $accountId): JsonResponse
    {
        $account = $this->getAccount($accountId);

        $rootButtons = $this->menuService->getMenuTree($account, true);
        $preview = MenuPreviewDTO::fromMenuButtons($rootButtons);

        return $this->json([
            'success' => true,
            'data' => $preview,
        ]);
    }

    /**
     * 获取账号实体.
     */
    private function getAccount(string $accountId): Account
    {
        $account = $this->accountRepository->find($accountId);

        if (null === $account) {
            throw $this->createNotFoundException('公众号不存在');
        }

        return $account;
    }
}
