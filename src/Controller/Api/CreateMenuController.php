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

final class CreateMenuController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MenuButtonVersionRepository $menuButtonVersionRepository,
    ) {
    }

    #[Route(path: '/admin/menu/version/{id}/menu', name: 'admin_menu_version_create_menu', methods: ['POST'])]
    public function __invoke(MenuVersion $version, Request $request): JsonResponse
    {
        if (MenuVersionStatus::DRAFT !== $version->getStatus()) {
            return new JsonResponse(['error' => '只能编辑草稿状态的版本'], Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return new JsonResponse(['error' => '无效的请求数据'], Response::HTTP_BAD_REQUEST);
        }

        $typedData = $this->normalizeData($data);

        // 验证必填字段
        $validationError = $this->validateRequiredFields($typedData);
        if (null !== $validationError) {
            return $validationError;
        }

        $menuTypeResult = $this->parseMenuType($typedData['type']);
        if ($menuTypeResult instanceof JsonResponse) {
            return $menuTypeResult;
        }
        $menuType = $menuTypeResult;

        $menu = $this->createMenu($version, $typedData, $menuType);
        $this->setMenuParent($menu, $typedData, $version);

        $this->entityManager->persist($menu);
        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $menu->getId(),
            'message' => '菜单创建成功',
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function validateRequiredFields(array $data): ?JsonResponse
    {
        if (!isset($data['name']) || '' === $data['name']) {
            return new JsonResponse(['error' => '菜单名称不能为空'], Response::HTTP_BAD_REQUEST);
        }

        if (!isset($data['type']) || '' === $data['type']) {
            return new JsonResponse(['error' => '菜单类型不能为空'], Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    /**
     * @return MenuType|JsonResponse
     */
    private function parseMenuType(mixed $type): MenuType|JsonResponse
    {
        if (!is_string($type) && !is_int($type)) {
            return new JsonResponse(['error' => '菜单类型必须为字符串或整数'], Response::HTTP_BAD_REQUEST);
        }

        try {
            return MenuType::from($type);
        } catch (\ValueError $e) {
            return new JsonResponse(['error' => '无效的菜单类型'], Response::HTTP_BAD_REQUEST);
        }
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
    private function createMenu(MenuVersion $version, array $data, MenuType $menuType): MenuButtonVersion
    {
        $menu = new MenuButtonVersion();
        $menu->setVersion($version);
        $menu->setName($this->getString($data, 'name'));
        $menu->setType($menuType);

        $this->setMenuProperties($menu, $data);

        return $menu;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function setMenuProperties(MenuButtonVersion $menu, array $data): void
    {
        $this->setStringProperties($menu, $data);
        $this->setBooleanAndIntProperties($menu, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function setStringProperties(MenuButtonVersion $menu, array $data): void
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
    private function setBooleanAndIntProperties(MenuButtonVersion $menu, array $data): void
    {
        $menu->setPosition(isset($data['position']) && is_int($data['position']) ? $data['position'] : 0);
        $menu->setEnabled(isset($data['enabled']) && is_bool($data['enabled']) ? $data['enabled'] : true);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function getString(array $data, string $key): string
    {
        if (!isset($data[$key])) {
            return '';
        }

        $value = $data[$key];

        return match (true) {
            is_string($value) => $value,
            is_scalar($value) => (string) $value,
            default => '',
        };
    }

    /**
     * @param array<string, mixed> $data
     */
    private function setMenuParent(MenuButtonVersion $menu, array $data, MenuVersion $version): void
    {
        if (!isset($data['parentId']) || '' === $data['parentId'] || 0 === $data['parentId']) {
            return;
        }

        $parent = $this->menuButtonVersionRepository->find($data['parentId']);
        if (null !== $parent && $parent->getVersion() === $version) {
            $menu->setParent($parent);
        }
    }
}
