<?php

namespace WechatOfficialAccountMenuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use WechatOfficialAccountMenuBundle\Repository\MenuButtonRepository;
use WechatOfficialAccountMenuBundle\Service\MenuService;

final class DeleteMenuController extends AbstractController
{
    public function __construct(
        private readonly MenuService $menuService,
        private readonly MenuButtonRepository $menuButtonRepository,
    ) {
    }

    /**
     * 删除菜单.
     */
    #[Route(path: '/api/wechat/menu/delete/{id}', name: 'wechat_menu_delete', methods: ['DELETE'])]
    public function __invoke(string $id): JsonResponse
    {
        $menuButton = $this->menuButtonRepository->find($id);
        if (null === $menuButton) {
            return $this->json([
                'success' => false,
                'error' => '菜单不存在',
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->menuService->deleteMenuButton($menuButton);

            return $this->json([
                'success' => true,
                'message' => '菜单删除成功',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
