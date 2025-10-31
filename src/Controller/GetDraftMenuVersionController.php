<?php

namespace WechatOfficialAccountMenuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Repository\AccountRepository;
use WechatOfficialAccountMenuBundle\DTO\MenuVersionListDTO;
use WechatOfficialAccountMenuBundle\Repository\MenuVersionRepository;

final class GetDraftMenuVersionController extends AbstractController
{
    public function __construct(
        private readonly MenuVersionRepository $menuVersionRepository,
        private readonly AccountRepository $accountRepository,
    ) {
    }

    /**
     * 获取最新草稿版本.
     */
    #[Route(path: '/api/wechat/menu-version/draft/{accountId}', name: 'wechat_menu_version_draft', methods: ['GET'])]
    public function __invoke(string $accountId): JsonResponse
    {
        $account = $this->getAccount($accountId);

        $draftVersion = $this->menuVersionRepository->findLatestDraftVersion($account);

        if (null === $draftVersion) {
            return $this->json([
                'success' => true,
                'data' => null,
                'message' => '当前没有草稿版本',
            ]);
        }

        return $this->json([
            'success' => true,
            'data' => MenuVersionListDTO::fromMenuVersion($draftVersion),
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
