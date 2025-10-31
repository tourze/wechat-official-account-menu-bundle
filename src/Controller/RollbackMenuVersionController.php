<?php

namespace WechatOfficialAccountMenuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use WechatOfficialAccountMenuBundle\DTO\MenuVersionListDTO;
use WechatOfficialAccountMenuBundle\Repository\MenuVersionRepository;
use WechatOfficialAccountMenuBundle\Service\MenuVersionService;

final class RollbackMenuVersionController extends AbstractController
{
    public function __construct(
        private readonly MenuVersionService $menuVersionService,
        private readonly MenuVersionRepository $menuVersionRepository,
    ) {
    }

    /**
     * 回滚到指定版本.
     */
    #[Route(path: '/api/wechat/menu-version/rollback/{id}', name: 'wechat_menu_version_rollback', methods: ['POST'])]
    public function __invoke(string $id): JsonResponse
    {
        $version = $this->menuVersionRepository->find($id);
        if (null === $version) {
            return $this->json([
                'success' => false,
                'error' => '版本不存在',
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $newVersion = $this->menuVersionService->rollbackToVersion($version);

            return $this->json([
                'success' => true,
                'message' => '回滚成功，已创建新的草稿版本',
                'data' => MenuVersionListDTO::fromMenuVersion($newVersion),
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
