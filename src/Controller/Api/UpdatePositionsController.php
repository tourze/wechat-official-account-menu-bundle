<?php

namespace WechatOfficialAccountMenuBundle\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use WechatOfficialAccountMenuBundle\Entity\MenuButtonVersion;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;
use WechatOfficialAccountMenuBundle\Enum\MenuVersionStatus;
use WechatOfficialAccountMenuBundle\Repository\MenuButtonVersionRepository;

final class UpdatePositionsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MenuButtonVersionRepository $menuButtonVersionRepository,
    ) {
    }

    #[Route(path: '/admin/menu/version/{id}/positions', name: 'admin_menu_version_update_positions', methods: ['POST'])]
    public function __invoke(MenuVersion $version, Request $request): JsonResponse
    {
        if (MenuVersionStatus::DRAFT !== $version->getStatus()) {
            return new JsonResponse(['error' => '只能编辑草稿状态的版本'], Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return new JsonResponse(['error' => '无效的请求数据'], Response::HTTP_BAD_REQUEST);
        }

        $positions = isset($data['positions']) && is_array($data['positions']) ? $data['positions'] : [];
        $typedPositions = $this->normalizePositions($positions);

        $this->updateMenuPositions($typedPositions, $version);

        $this->entityManager->flush();

        return new JsonResponse(['message' => '排序更新成功']);
    }

    /**
     * @param array<string, mixed> $positions
     */
    private function updateMenuPositions(array $positions, MenuVersion $version): void
    {
        foreach ($positions as $menuId => $info) {
            $menu = $this->menuButtonVersionRepository->find($menuId);

            if (null === $menu || $menu->getVersion() !== $version) {
                continue;
            }

            if (!is_array($info)) {
                continue;
            }

            $typedInfo = $this->normalizeInfo($info);
            $position = isset($typedInfo['position']) && is_int($typedInfo['position']) ? $typedInfo['position'] : 0;
            $menu->setPosition($position);
            $this->updateMenuParentRelation($menu, $typedInfo, $version);
        }
    }

    /**
     * @param array<string, mixed> $info
     */
    private function updateMenuParentRelation(MenuButtonVersion $menu, array $info, MenuVersion $version): void
    {
        if (!isset($info['parentId'])) {
            return;
        }

        if ('' === $info['parentId'] || 0 === $info['parentId']) {
            $menu->setParent(null);

            return;
        }

        $parent = $this->menuButtonVersionRepository->find($info['parentId']);
        if (null !== $parent && $parent->getVersion() === $version) {
            $menu->setParent($parent);
        }
    }

    /**
     * @param array<mixed, mixed> $positions
     * @return array<string, mixed>
     */
    private function normalizePositions(array $positions): array
    {
        $normalized = [];
        foreach ($positions as $key => $value) {
            $stringKey = is_string($key) ? $key : (string) $key;
            $normalized[$stringKey] = $value;
        }

        return $normalized;
    }

    /**
     * @param array<mixed, mixed> $info
     * @return array<string, mixed>
     */
    private function normalizeInfo(array $info): array
    {
        $normalized = [];
        foreach ($info as $key => $value) {
            $stringKey = is_string($key) ? $key : (string) $key;
            $normalized[$stringKey] = $value;
        }

        return $normalized;
    }
}
