<?php

namespace WechatOfficialAccountMenuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use WechatOfficialAccountMenuBundle\DTO\MenuVersionListDTO;
use WechatOfficialAccountMenuBundle\Repository\MenuVersionRepository;
use WechatOfficialAccountMenuBundle\Service\MenuVersionService;

final class PublishMenuVersionController extends AbstractController
{
    public function __construct(
        private readonly MenuVersionService $menuVersionService,
        private readonly MenuVersionRepository $menuVersionRepository,
    ) {
    }

    /**
     * 发布版本.
     */
    #[Route(path: '/api/wechat/menu-version/publish/{id}', name: 'wechat_menu_version_publish', methods: ['POST'])]
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
            $this->menuVersionService->publishVersion($version);

            return $this->json([
                'success' => true,
                'message' => '版本发布成功',
                'data' => MenuVersionListDTO::fromMenuVersion($version),
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
