<?php

namespace WechatOfficialAccountMenuBundle\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;

final class GetMenusController extends AbstractController
{
    #[Route(path: '/admin/menu/version/{id}/menus', name: 'admin_menu_version_get_menus', methods: ['GET'])]
    public function __invoke(MenuVersion $version): JsonResponse
    {
        $menus = [];

        foreach ($version->getButtons() as $button) {
            $menus[] = [
                'id' => $button->getId(),
                'parentId' => $button->getParent()?->getId(),
                'name' => $button->getName(),
                'type' => $button->getType()?->value,
                'clickKey' => $button->getClickKey(),
                'url' => $button->getUrl(),
                'appId' => $button->getAppId(),
                'pagePath' => $button->getPagePath(),
                'mediaId' => $button->getMediaId(),
                'position' => $button->getPosition(),
                'enabled' => $button->isEnabled(),
            ];
        }

        return new JsonResponse($menus);
    }
}
