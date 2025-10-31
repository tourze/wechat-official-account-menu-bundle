<?php

namespace WechatOfficialAccountMenuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use WechatOfficialAccountMenuBundle\DTO\MenuTreeDTO;
use WechatOfficialAccountMenuBundle\Repository\MenuButtonRepository;
use WechatOfficialAccountMenuBundle\Service\MenuService;

final class MoveMenuController extends AbstractController
{
    public function __construct(
        private readonly MenuService $menuService,
        private readonly MenuButtonRepository $menuButtonRepository,
    ) {
    }

    /**
     * 移动菜单.
     */
    #[Route(path: '/api/wechat/menu/move/{id}', name: 'wechat_menu_move', methods: ['PATCH'])]
    public function __invoke(string $id, Request $request): JsonResponse
    {
        $menuButton = $this->menuButtonRepository->find($id);
        if (null === $menuButton) {
            return $this->json([
                'success' => false,
                'error' => '菜单不存在',
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        assert(is_array($data));

        $newParentId = $data['newParentId'] ?? null;
        assert(is_string($newParentId) || null === $newParentId);

        $newParent = null;
        if (null !== $newParentId) {
            $newParent = $this->menuButtonRepository->find($newParentId);
            if (null === $newParent || $newParent->getAccount()->getId() !== $menuButton->getAccount()->getId()) {
                return $this->json([
                    'success' => false,
                    'error' => '目标父菜单不存在',
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        try {
            $this->menuService->moveMenuButton($menuButton, $newParent);

            return $this->json([
                'success' => true,
                'data' => MenuTreeDTO::fromMenuButton($menuButton),
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
