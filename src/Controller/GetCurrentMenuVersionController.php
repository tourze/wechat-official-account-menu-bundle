<?php

namespace WechatOfficialAccountMenuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Repository\AccountRepository;
use WechatOfficialAccountMenuBundle\DTO\MenuVersionListDTO;
use WechatOfficialAccountMenuBundle\Repository\MenuVersionRepository;

final class GetCurrentMenuVersionController extends AbstractController
{
    public function __construct(
        private readonly MenuVersionRepository $menuVersionRepository,
        private readonly AccountRepository $accountRepository,
    ) {
    }

    /**
     * 获取当前发布版本.
     */
    #[Route(path: '/api/wechat/menu-version/current/{accountId}', name: 'wechat_menu_version_current', methods: ['GET'])]
    public function __invoke(string $accountId): JsonResponse
    {
        $account = $this->getAccount($accountId);

        $currentVersion = $this->menuVersionRepository->findCurrentPublishedVersion($account);

        if (null === $currentVersion) {
            return $this->json([
                'success' => true,
                'data' => null,
                'message' => '当前没有已发布的版本',
            ]);
        }

        return $this->json([
            'success' => true,
            'data' => MenuVersionListDTO::fromMenuVersion($currentVersion),
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
