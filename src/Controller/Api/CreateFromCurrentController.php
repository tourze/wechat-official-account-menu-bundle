<?php

namespace WechatOfficialAccountMenuBundle\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Exception\MenuVersionException;
use WechatOfficialAccountMenuBundle\Service\MenuVersionService;

final class CreateFromCurrentController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MenuVersionService $versionService,
    ) {
    }

    #[Route(path: '/admin/menu/version/{id}/from-current', name: 'admin_menu_version_create_from_current', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return new JsonResponse(['error' => '无效的 JSON 格式'], Response::HTTP_BAD_REQUEST);
        }

        $accountId = $data['accountId'] ?? null;
        assert(is_string($accountId) || null === $accountId);

        $name = $data['name'] ?? null;
        assert(is_string($name) || null === $name);

        $description = $data['description'] ?? null;
        assert(is_string($description) || null === $description);

        if (null === $accountId || '' === $accountId || null === $name || '' === $name) {
            return new JsonResponse(['error' => '参数不完整'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $account = $this->entityManager->getReference('WechatOfficialAccountBundle\Entity\Account', $accountId);
            if (!$account instanceof Account) {
                throw new MenuVersionException('Invalid account ID');
            }
            $version = $this->versionService->createVersion($account, $description);

            return new JsonResponse([
                'id' => $version->getId(),
                'message' => '版本创建成功',
                'redirectUrl' => '/admin',
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
