<?php

namespace WechatOfficialAccountMenuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Repository\AccountRepository;
use WechatOfficialAccountMenuBundle\Service\MenuService;

final class ValidateMenuController extends AbstractController
{
    public function __construct(
        private readonly MenuService $menuService,
        private readonly AccountRepository $accountRepository,
    ) {
    }

    /**
     * 验证菜单结构.
     */
    #[Route(path: '/api/wechat/menu/validate/{accountId}', name: 'wechat_menu_validate', methods: ['GET'])]
    public function __invoke(string $accountId): JsonResponse
    {
        $account = $this->getAccount($accountId);

        $errors = $this->menuService->validateMenuStructure($account);

        return $this->json([
            'success' => [] === $errors,
            'errors' => $errors,
        ]);
    }

    /**
     * 获取账号实体.
     */
    private function getAccount(string $accountId): Account
    {
        // 将字符串ID转换为整型，因为Account的ID是int类型
        if (!is_numeric($accountId)) {
            throw $this->createNotFoundException('公众号不存在');
        }

        $account = $this->accountRepository->find((int) $accountId);

        if (null === $account) {
            throw $this->createNotFoundException('公众号不存在');
        }

        return $account;
    }
}
