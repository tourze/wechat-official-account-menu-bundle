<?php

namespace WechatOfficialAccountMenuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use WechatOfficialAccountMenuBundle\DTO\MenuTreeDTO;
use WechatOfficialAccountMenuBundle\DTO\MenuVersionListDTO;
use WechatOfficialAccountMenuBundle\Repository\MenuVersionRepository;

final class GetMenuVersionController extends AbstractController
{
    public function __construct(
        private readonly MenuVersionRepository $menuVersionRepository,
    ) {
    }

    /**
     * 获取版本详情.
     */
    #[Route(path: '/api/wechat/menu-version/{id}', name: 'wechat_menu_version_detail', methods: ['GET'])]
    public function __invoke(string $id): JsonResponse
    {
        $version = $this->menuVersionRepository->find($id);
        if (null === $version) {
            return $this->json([
                'success' => false,
                'error' => '版本不存在',
            ], Response::HTTP_NOT_FOUND);
        }

        $buttons = [];
        foreach ($version->getRootButtons() as $button) {
            $buttons[] = MenuTreeDTO::fromMenuButtonVersion($button);
        }

        return $this->json([
            'success' => true,
            'data' => [
                'version' => MenuVersionListDTO::fromMenuVersion($version),
                'buttons' => $buttons,
            ],
        ]);
    }
}
