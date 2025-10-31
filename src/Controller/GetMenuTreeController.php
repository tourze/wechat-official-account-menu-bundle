<?php

namespace WechatOfficialAccountMenuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Repository\AccountRepository;
use WechatOfficialAccountMenuBundle\DTO\MenuTreeDTO;
use WechatOfficialAccountMenuBundle\Entity\MenuButton;
use WechatOfficialAccountMenuBundle\Service\MenuService;

final class GetMenuTreeController extends AbstractController
{
    public function __construct(
        private readonly MenuService $menuService,
        private readonly AccountRepository $accountRepository,
    ) {
    }

    /**
     * 获取菜单树.
     */
    #[Route(path: '/api/wechat/menu/tree/{accountId}', name: 'wechat_menu_tree', methods: ['GET'])]
    public function __invoke(string $accountId, Request $request): JsonResponse
    {
        $account = $this->getAccount($accountId);
        $onlyEnabled = $request->query->getBoolean('onlyEnabled', true);

        $rootButtons = $this->menuService->getMenuTree($account, $onlyEnabled);

        $tree = array_map(
            fn (MenuButton $button) => MenuTreeDTO::fromMenuButton($button),
            $rootButtons
        );

        return $this->json([
            'success' => true,
            'data' => $tree,
        ]);
    }

    private function getAccount(string $accountId): Account
    {
        $account = $this->accountRepository->find($accountId);

        if (null === $account) {
            throw $this->createNotFoundException('公众号不存在');
        }

        return $account;
    }
}
