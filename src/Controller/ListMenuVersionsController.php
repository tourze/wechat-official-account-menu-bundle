<?php

namespace WechatOfficialAccountMenuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Repository\AccountRepository;
use WechatOfficialAccountMenuBundle\DTO\MenuVersionListDTO;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;
use WechatOfficialAccountMenuBundle\Repository\MenuVersionRepository;

final class ListMenuVersionsController extends AbstractController
{
    public function __construct(
        private readonly MenuVersionRepository $menuVersionRepository,
        private readonly AccountRepository $accountRepository,
    ) {
    }

    /**
     * 获取版本列表.
     */
    #[Route(path: '/api/wechat/menu-version/list/{accountId}', name: 'wechat_menu_version_list', methods: ['GET'])]
    public function __invoke(string $accountId): JsonResponse
    {
        $account = $this->getAccount($accountId);

        $versions = $this->menuVersionRepository->findByAccount($account);

        $list = array_map(
            fn (MenuVersion $version) => MenuVersionListDTO::fromMenuVersion($version),
            $versions
        );

        return $this->json([
            'success' => true,
            'data' => $list,
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
