<?php

namespace WechatOfficialAccountMenuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Repository\AccountRepository;
use WechatOfficialAccountMenuBundle\DTO\MenuVersionListDTO;
use WechatOfficialAccountMenuBundle\Repository\MenuVersionRepository;
use WechatOfficialAccountMenuBundle\Service\MenuVersionService;

final class CreateMenuVersionController extends AbstractController
{
    public function __construct(
        private readonly MenuVersionService $menuVersionService,
        private readonly MenuVersionRepository $menuVersionRepository,
        private readonly AccountRepository $accountRepository,
    ) {
    }

    /**
     * 创建新版本.
     */
    #[Route(path: '/api/wechat/menu-version/create/{accountId}', name: 'wechat_menu_version_create', methods: ['POST'])]
    public function __invoke(string $accountId, Request $request): JsonResponse
    {
        $account = $this->getAccount($accountId);

        $data = json_decode($request->getContent(), true);
        assert(is_array($data));

        $description = $data['description'] ?? null;
        assert(is_string($description) || null === $description);

        $copyFromId = $data['copyFromId'] ?? null;
        assert(is_string($copyFromId) || null === $copyFromId);

        $copyFrom = null;
        if (null !== $copyFromId) {
            $copyFrom = $this->menuVersionRepository->find($copyFromId);
            if (null === $copyFrom || (string) $copyFrom->getAccount()->getId() !== $accountId) {
                return $this->json([
                    'success' => false,
                    'error' => '源版本不存在',
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        try {
            $version = $this->menuVersionService->createVersion($account, $description, $copyFrom);

            return $this->json([
                'success' => true,
                'data' => MenuVersionListDTO::fromMenuVersion($version),
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
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
