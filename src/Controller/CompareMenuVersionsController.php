<?php

namespace WechatOfficialAccountMenuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use WechatOfficialAccountMenuBundle\DTO\MenuVersionListDTO;
use WechatOfficialAccountMenuBundle\Repository\MenuVersionRepository;
use WechatOfficialAccountMenuBundle\Service\MenuVersionService;

final class CompareMenuVersionsController extends AbstractController
{
    public function __construct(
        private readonly MenuVersionService $menuVersionService,
        private readonly MenuVersionRepository $menuVersionRepository,
    ) {
    }

    /**
     * 对比两个版本.
     */
    #[Route(path: '/api/wechat/menu-version/compare', name: 'wechat_menu_version_compare', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $version1Id = $request->query->get('version1');
        $version2Id = $request->query->get('version2');

        if (null === $version1Id || null === $version2Id || '' === $version1Id || '' === $version2Id) {
            return $this->json([
                'success' => false,
                'error' => '请提供两个版本ID',
            ], Response::HTTP_BAD_REQUEST);
        }

        $version1 = $this->menuVersionRepository->find($version1Id);
        $version2 = $this->menuVersionRepository->find($version2Id);

        if (null === $version1 || null === $version2) {
            return $this->json([
                'success' => false,
                'error' => '版本不存在',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($version1->getAccount()->getId() !== $version2->getAccount()->getId()) {
            return $this->json([
                'success' => false,
                'error' => '只能对比同一公众号的版本',
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $changes = $this->menuVersionService->compareVersions($version1, $version2);

            return $this->json([
                'success' => true,
                'data' => [
                    'version1' => MenuVersionListDTO::fromMenuVersion($version1),
                    'version2' => MenuVersionListDTO::fromMenuVersion($version2),
                    'changes' => $changes,
                ],
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
