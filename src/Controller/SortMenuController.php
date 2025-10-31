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
use WechatOfficialAccountMenuBundle\DTO\MenuSortDTO;
use WechatOfficialAccountMenuBundle\Service\MenuService;

final class SortMenuController extends AbstractController
{
    public function __construct(
        private readonly MenuService $menuService,
        private readonly ValidatorInterface $validator,
        private readonly AccountRepository $accountRepository,
    ) {
    }

    /**
     * 更新菜单排序.
     */
    #[Route(path: '/api/wechat/menu/sort/{accountId}', name: 'wechat_menu_sort', methods: ['PATCH'])]
    public function __invoke(string $accountId, Request $request): JsonResponse
    {
        $account = $this->getAccount($accountId);

        $dto = new MenuSortDTO();
        $data = json_decode($request->getContent(), true);
        assert(is_array($data));

        $rawItems = $data['items'] ?? [];
        assert(is_array($rawItems));

        // 验证并转换items为正确的类型
        $items = [];
        foreach ($rawItems as $item) {
            assert(is_array($item));
            assert(isset($item['id']) && is_string($item['id']));
            assert(isset($item['position']) && is_int($item['position']));
            $items[] = ['id' => $item['id'], 'position' => $item['position']];
        }
        $dto->items = $items;

        // 验证
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json([
                'success' => false,
                'errors' => $this->formatValidationErrors($errors),
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->menuService->updateMenuPositions($dto->getPositionMap());

            return $this->json([
                'success' => true,
                'message' => '排序更新成功',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * 获取账号实体.
     */
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
