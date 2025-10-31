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

final class ToggleMenuController extends AbstractController
{
    public function __construct(
        private readonly MenuService $menuService,
        private readonly MenuButtonRepository $menuButtonRepository,
    ) {
    }

    /**
     * 切换菜单状态.
     */
    #[Route(path: '/api/wechat/menu/toggle/{id}', name: 'wechat_menu_toggle', methods: ['PATCH'])]
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

        $enabled = $data['enabled'] ?? !$menuButton->isEnabled();
        assert(is_bool($enabled));

        try {
            $this->menuService->toggleMenuButton($menuButton, $enabled);

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
