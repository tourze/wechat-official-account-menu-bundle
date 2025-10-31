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
use WechatOfficialAccountMenuBundle\Enum\MenuType;
use WechatOfficialAccountMenuBundle\Enum\MenuVersionStatus;
use WechatOfficialAccountMenuBundle\Repository\MenuButtonVersionRepository;

final class UpdateMenuController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MenuButtonVersionRepository $menuButtonVersionRepository,
    ) {
    }

    #[Route(path: '/admin/menu/version/{id}/menu/{menuId}', name: 'admin_menu_version_save_menu', methods: ['PUT'])]
    public function __invoke(MenuVersion $version, string $menuId, Request $request): JsonResponse
    {
        if (MenuVersionStatus::DRAFT !== $version->getStatus()) {
            return new JsonResponse(['error' => '只能编辑草稿状态的版本'], Response::HTTP_BAD_REQUEST);
        }

        $menu = $this->menuButtonVersionRepository->find($menuId);

        if (null === $menu || $menu->getVersion() !== $version) {
            return new JsonResponse(['error' => '菜单不存在'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return new JsonResponse(['error' => '无效的请求数据'], Response::HTTP_BAD_REQUEST);
        }

        $typedData = $this->normalizeData($data);
        $this->updateMenuFields($menu, $typedData);
        $this->updateMenuParent($menu, $typedData, $version);

        $this->entityManager->flush();

        return new JsonResponse(['message' => '菜单更新成功']);
    }

    /**
     * @param array<mixed, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeData(array $data): array
    {
        $normalized = [];
        foreach ($data as $key => $value) {
            $stringKey = is_string($key) ? $key : (string) $key;
            $normalized[$stringKey] = $value;
        }

        return $normalized;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateMenuFields(MenuButtonVersion $menu, array $data): void
    {
        $this->updateBasicFields($menu, $data);
        $this->updateOptionalFields($menu, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateBasicFields(MenuButtonVersion $menu, array $data): void
    {
        if (isset($data['name'])) {
            $name = $data['name'];
            $stringName = match (true) {
                is_string($name) => $name,
                is_scalar($name) => (string) $name,
                default => '',
            };
            $menu->setName($stringName);
        }

        if (isset($data['type']) && is_string($data['type'])) {
            $menuType = MenuType::tryFrom($data['type']);
            if (null !== $menuType) {
                $menu->setType($menuType);
            }
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateOptionalFields(MenuButtonVersion $menu, array $data): void
    {
        $this->updateStringFields($menu, $data);
        $this->updateBooleanAndIntFields($menu, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateStringFields(MenuButtonVersion $menu, array $data): void
    {
        $menu->setClickKey(isset($data['clickKey']) && is_string($data['clickKey']) ? $data['clickKey'] : null);
        $menu->setUrl(isset($data['url']) && is_string($data['url']) ? $data['url'] : null);
        $menu->setAppId(isset($data['appId']) && is_string($data['appId']) ? $data['appId'] : null);
        $menu->setPagePath(isset($data['pagePath']) && is_string($data['pagePath']) ? $data['pagePath'] : null);
        $menu->setMediaId(isset($data['mediaId']) && is_string($data['mediaId']) ? $data['mediaId'] : null);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateBooleanAndIntFields(MenuButtonVersion $menu, array $data): void
    {
        $menu->setPosition(isset($data['position']) && is_int($data['position']) ? $data['position'] : 0);
        $menu->setEnabled(isset($data['enabled']) && is_bool($data['enabled']) ? $data['enabled'] : true);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateMenuParent(MenuButtonVersion $menu, array $data, MenuVersion $version): void
    {
        if (!isset($data['parentId']) || $data['parentId'] === $menu->getParent()?->getId()) {
            return;
        }

        if ('' === $data['parentId'] || 0 === $data['parentId']) {
            $menu->setParent(null);

            return;
        }

        $parent = $this->menuButtonVersionRepository->find($data['parentId']);
        if (null !== $parent && $parent->getVersion() === $version) {
            $menu->setParent($parent);
        }
    }
}
