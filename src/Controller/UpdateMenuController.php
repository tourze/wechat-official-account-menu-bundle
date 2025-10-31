<?php

namespace WechatOfficialAccountMenuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use WechatOfficialAccountMenuBundle\DTO\MenuFormDTO;
use WechatOfficialAccountMenuBundle\DTO\MenuTreeDTO;
use WechatOfficialAccountMenuBundle\Enum\MenuType;
use WechatOfficialAccountMenuBundle\Repository\MenuButtonRepository;
use WechatOfficialAccountMenuBundle\Service\MenuService;

final class UpdateMenuController extends AbstractController
{
    public function __construct(
        private readonly MenuService $menuService,
        private readonly MenuButtonRepository $menuButtonRepository,
        private readonly ValidatorInterface $validator,
    ) {
    }

    /**
     * 更新菜单.
     */
    #[Route(path: '/api/wechat/menu/update/{id}', name: 'wechat_menu_update', methods: ['PUT'])]
    public function __invoke(string $id, Request $request): JsonResponse
    {
        $menuButton = $this->menuButtonRepository->find($id);
        if (null === $menuButton) {
            return $this->json([
                'success' => false,
                'error' => '菜单不存在',
            ], Response::HTTP_NOT_FOUND);
        }

        $dto = new MenuFormDTO();
        $data = json_decode($request->getContent(), true);
        assert(is_array($data));

        // 填充DTO
        $name = $data['name'] ?? $menuButton->getName();
        assert(is_string($name) || null === $name);
        $dto->name = $name;

        $type = $data['type'] ?? null;
        if (null !== $type) {
            assert(is_int($type) || is_string($type));
            $dto->type = MenuType::from($type);
        } else {
            $dto->type = $menuButton->getType();
        }

        $clickKey = $data['clickKey'] ?? $menuButton->getClickKey();
        assert(is_string($clickKey) || null === $clickKey);
        $dto->clickKey = $clickKey;

        $url = $data['url'] ?? $menuButton->getUrl();
        assert(is_string($url) || null === $url);
        $dto->url = $url;

        $appId = $data['appId'] ?? $menuButton->getAppId();
        assert(is_string($appId) || null === $appId);
        $dto->appId = $appId;

        $pagePath = $data['pagePath'] ?? $menuButton->getPagePath();
        assert(is_string($pagePath) || null === $pagePath);
        $dto->pagePath = $pagePath;

        $position = $data['position'] ?? $menuButton->getPosition();
        assert(is_int($position));
        $dto->position = $position;

        $enabled = $data['enabled'] ?? $menuButton->isEnabled();
        assert(is_bool($enabled));
        $dto->enabled = $enabled;

        // 验证
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json([
                'success' => false,
                'errors' => $this->formatValidationErrors($errors),
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $menuButton = $this->menuService->updateMenuButton($menuButton, $dto->toArray());

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

    /**
     * 格式化验证错误.
     *
     * @param ConstraintViolationListInterface $errors
     *
     * @return array<string, string>
     */
    private function formatValidationErrors($errors): array
    {
        $formattedErrors = [];
        foreach ($errors as $error) {
            $formattedErrors[$error->getPropertyPath()] = $error->getMessage();
        }

        return $formattedErrors;
    }
}
