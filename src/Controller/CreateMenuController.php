<?php

namespace WechatOfficialAccountMenuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Repository\AccountRepository;
use WechatOfficialAccountMenuBundle\DTO\MenuFormDTO;
use WechatOfficialAccountMenuBundle\DTO\MenuTreeDTO;
use WechatOfficialAccountMenuBundle\Enum\MenuType;
use WechatOfficialAccountMenuBundle\Repository\MenuButtonRepository;
use WechatOfficialAccountMenuBundle\Service\MenuService;

final class CreateMenuController extends AbstractController
{
    public function __construct(
        private readonly MenuService $menuService,
        private readonly MenuButtonRepository $menuButtonRepository,
        private readonly ValidatorInterface $validator,
        private readonly AccountRepository $accountRepository,
    ) {
    }

    /**
     * 创建菜单.
     */
    #[Route(path: '/api/wechat/menu/create/{accountId}', name: 'wechat_menu_create', methods: ['POST'])]
    public function __invoke(string $accountId, Request $request): JsonResponse
    {
        $account = $this->getAccount($accountId);

        $dto = new MenuFormDTO();
        $data = json_decode($request->getContent(), true);
        assert(is_array($data));

        // 填充DTO
        $name = $data['name'] ?? null;
        assert(is_string($name) || null === $name);
        $dto->name = $name;

        $type = $data['type'] ?? null;
        if (null !== $type) {
            assert(is_int($type) || is_string($type));
            $dto->type = MenuType::from($type);
        } else {
            $dto->type = null;
        }

        $parentId = $data['parentId'] ?? null;
        assert(is_string($parentId) || null === $parentId);
        $dto->parentId = $parentId;

        $clickKey = $data['clickKey'] ?? null;
        assert(is_string($clickKey) || null === $clickKey);
        $dto->clickKey = $clickKey;

        $url = $data['url'] ?? null;
        assert(is_string($url) || null === $url);
        $dto->url = $url;

        $appId = $data['appId'] ?? null;
        assert(is_string($appId) || null === $appId);
        $dto->appId = $appId;

        $pagePath = $data['pagePath'] ?? null;
        assert(is_string($pagePath) || null === $pagePath);
        $dto->pagePath = $pagePath;

        $position = $data['position'] ?? 0;
        assert(is_int($position));
        $dto->position = $position;

        $enabled = $data['enabled'] ?? true;
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

        // 处理父菜单
        $parent = null;
        if (null !== $dto->parentId) {
            $parent = $this->menuButtonRepository->find($dto->parentId);
            if (null === $parent || $parent->getAccount()->getId() !== $accountId) {
                return $this->json([
                    'success' => false,
                    'error' => '父菜单不存在',
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $menuData = $dto->toArray();
        if (null !== $parent) {
            $menuData['parent'] = $parent;
        }

        try {
            $menuButton = $this->menuService->createMenuButton($account, $menuData);

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

    private function getAccount(string $accountId): Account
    {
        $account = $this->accountRepository->find($accountId);

        if (null === $account) {
            throw $this->createNotFoundException('公众号不存在');
        }

        return $account;
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
