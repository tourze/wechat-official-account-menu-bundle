<?php

namespace WechatOfficialAccountMenuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use WechatOfficialAccountMenuBundle\DTO\MenuPreviewDTO;
use WechatOfficialAccountMenuBundle\Repository\MenuVersionRepository;

final class PreviewMenuVersionController extends AbstractController
{
    public function __construct(
        private readonly MenuVersionRepository $menuVersionRepository,
    ) {
    }

    /**
     * 预览版本.
     */
    #[Route(path: '/api/wechat/menu-version/preview/{id}', name: 'wechat_menu_version_preview', methods: ['GET'])]
    public function __invoke(string $id): JsonResponse
    {
        $version = $this->menuVersionRepository->find($id);
        if (null === $version) {
            return $this->json([
                'success' => false,
                'error' => '版本不存在',
            ], Response::HTTP_NOT_FOUND);
        }

        $preview = MenuPreviewDTO::fromMenuVersion($version);

        return $this->json([
            'success' => true,
            'data' => $preview,
        ]);
    }
}
