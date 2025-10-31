<?php

namespace WechatOfficialAccountMenuBundle\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;
use WechatOfficialAccountMenuBundle\Enum\MenuVersionStatus;
use WechatOfficialAccountMenuBundle\Repository\MenuButtonVersionRepository;

final class DeleteMenuController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MenuButtonVersionRepository $menuButtonVersionRepository,
    ) {
    }

    #[Route(path: '/admin/menu/version/{id}/menu/{menuId}', name: 'admin_menu_version_delete_menu', methods: ['DELETE'])]
    public function __invoke(MenuVersion $version, string $menuId): JsonResponse
    {
        if (MenuVersionStatus::DRAFT !== $version->getStatus()) {
            return new JsonResponse(['error' => '只能编辑草稿状态的版本'], Response::HTTP_BAD_REQUEST);
        }

        $menu = $this->menuButtonVersionRepository->find($menuId);

        if (null === $menu || $menu->getVersion() !== $version) {
            return new JsonResponse(['error' => '菜单不存在'], Response::HTTP_NOT_FOUND);
        }

        if (!$menu->getChildren()->isEmpty()) {
            return new JsonResponse(['error' => '请先删除子菜单'], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->remove($menu);
        $this->entityManager->flush();

        return new JsonResponse(['message' => '菜单删除成功']);
    }
}
