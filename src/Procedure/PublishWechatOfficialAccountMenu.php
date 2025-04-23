<?php

namespace WechatOfficialAccountMenuBundle\Procedure;

use AntdCpBundle\Builder\Action\ApiCallAction;
use AppBundle\Procedure\Base\ApiCallActionProcedure;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPCLogBundle\Attribute\Log;
use Tourze\JsonRPCSecurityBundle\Attribute\MethodPermission;
use WechatOfficialAccountBundle\Service\OfficialAccountClient;
use WechatOfficialAccountMenuBundle\Entity\MenuButton;
use WechatOfficialAccountMenuBundle\Repository\MenuButtonRepository;
use WechatOfficialAccountMenuBundle\Request\AddConditionalMenuRequest;

#[Log]
#[MethodExpose(PublishWechatOfficialAccountMenu::NAME)]
#[IsGranted('ROLE_OPERATOR')]
#[MethodPermission(permission: MenuButton::class . '::renderPublishAction', title: '发布菜单')]
class PublishWechatOfficialAccountMenu extends ApiCallActionProcedure
{
    public const NAME = 'PublishWechatOfficialAccountMenu';

    public function __construct(
        private readonly MenuButtonRepository $buttonRepository,
        private readonly OfficialAccountClient $client,
    ) {
    }

    public function getAction(): ApiCallAction
    {
        return ApiCallAction::gen()
            ->setLabel('发布')
            ->setConfirmText('是否确认发布菜单到线上？')
            ->setApiName(PublishWechatOfficialAccountMenu::NAME);
    }

    public function execute(): array
    {
        $that = $this->buttonRepository->findOneBy(['id' => $this->id]);
        if (!$that) {
            throw new ApiException('找不到记录');
        }

        if ($that->getParent()) {
            throw new ApiException('该节点无法作为菜单发布');
        }

        $buttons = [];
        foreach ($that->getChildren() as $child) {
            $buttons[] = $child->toWechatFormat();
        }

        if (empty($buttons)) {
            throw new ApiException('菜单不能为空');
        }

        $request = new AddConditionalMenuRequest();
        $request->setAccount($that->getAccount());
        $request->setButtons($buttons);
        $request->setMatchRule([]);
        try {
            $res = $this->client->request($request);
        } catch (\Throwable $exception) {
            throw new ApiException($exception->getMessage(), previous: $exception);
        }

        return [
            '__message' => '发布成功',
            'buttons' => $buttons,
            'res' => $res,
        ];
    }
}
