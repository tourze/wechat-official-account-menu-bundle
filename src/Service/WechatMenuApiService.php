<?php

namespace WechatOfficialAccountMenuBundle\Service;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Service\OfficialAccountClient;
use WechatOfficialAccountMenuBundle\Exception\WechatApiException;
use WechatOfficialAccountMenuBundle\Request\Menu\AddConditionalMenuRequest;
use WechatOfficialAccountMenuBundle\Request\Menu\CreateMenuRequest;
use WechatOfficialAccountMenuBundle\Request\Menu\DeleteConditionalMenuRequest;
use WechatOfficialAccountMenuBundle\Request\Menu\DeleteMenuRequest;
use WechatOfficialAccountMenuBundle\Request\Menu\GetCurrentSelfMenuInfoRequest;
use WechatOfficialAccountMenuBundle\Request\Menu\GetMenuRequest;
use WechatOfficialAccountMenuBundle\Request\Menu\TryMatchMenuRequest;

#[WithMonologChannel(channel: 'wechat_official_account_menu')]
class WechatMenuApiService
{
    public function __construct(
        private readonly OfficialAccountClient $officialAccountClient,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * 创建自定义菜单.
     *
     * @param array<string, mixed> $menuData
     * @see https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Creating_Custom-Defined_Menu.html
     */
    public function createMenu(Account $account, array $menuData): void
    {
        $request = new CreateMenuRequest();
        $request->setAccount($account);
        $request->setMenuData($menuData);

        try {
            $this->officialAccountClient->request($request);

            $this->logger->info('成功创建微信菜单', [
                'account' => $account->getId(),
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('创建微信菜单失败', [
                'account' => $account->getId(),
                'error' => $e->getMessage(),
                'menuData' => $menuData,
            ]);

            throw new WechatApiException(sprintf('创建菜单失败: %s', $e->getMessage()), 0, $e);
        }
    }

    /**
     * 查询自定义菜单.
     *
     * @see https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Querying_Custom_Menus.html
     *
     * @return array<string, mixed>
     */
    public function getMenu(Account $account): array
    {
        $request = new GetMenuRequest();
        $request->setAccount($account);

        try {
            $result = $this->officialAccountClient->request($request);
            /** @var array<string, mixed> $result */
            assert(is_array($result));

            return $result;
        } catch (\Throwable $e) {
            $this->logger->error('查询微信菜单失败', [
                'account' => $account->getId(),
                'error' => $e->getMessage(),
            ]);

            throw new WechatApiException(sprintf('查询菜单失败: %s', $e->getMessage()), 0, $e);
        }
    }

    /**
     * 删除自定义菜单.
     *
     * @see https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Deleting_Custom-Defined_Menu.html
     */
    public function deleteMenu(Account $account): void
    {
        $request = new DeleteMenuRequest();
        $request->setAccount($account);

        try {
            $this->officialAccountClient->request($request);

            $this->logger->info('成功删除微信菜单', [
                'account' => $account->getId(),
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('删除微信菜单失败', [
                'account' => $account->getId(),
                'error' => $e->getMessage(),
            ]);

            throw new WechatApiException(sprintf('删除菜单失败: %s', $e->getMessage()), 0, $e);
        }
    }

    /**
     * 创建个性化菜单.
     *
     * @param array<string, mixed> $menuData
     * @param array<string, mixed> $matchRule
     * @see https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Personalized_menu_interface.html
     */
    public function createConditionalMenu(Account $account, array $menuData, array $matchRule): string
    {
        $request = new AddConditionalMenuRequest();
        $request->setAccount($account);
        $request->setMenuData($menuData);
        $request->setMatchRule($matchRule);

        try {
            $result = $this->officialAccountClient->request($request);
            /** @var array{menuid: string} $result */
            assert(is_array($result));

            if (!isset($result['menuid'])) {
                throw new WechatApiException('响应中缺少menuid字段', 0);
            }

            $menuId = $result['menuid'];

            $this->logger->info('成功创建个性化菜单', [
                'account' => $account->getId(),
                'menuId' => $menuId,
            ]);

            return $menuId;
        } catch (\Throwable $e) {
            $this->logger->error('创建个性化菜单失败', [
                'account' => $account->getId(),
                'error' => $e->getMessage(),
                'menuData' => $menuData,
                'matchRule' => $matchRule,
            ]);

            throw new WechatApiException(sprintf('创建个性化菜单失败: %s', $e->getMessage()), 0, $e);
        }
    }

    /**
     * 删除个性化菜单.
     *
     * @see https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Personalized_menu_interface.html
     */
    public function deleteConditionalMenu(Account $account, string $menuId): void
    {
        $request = new DeleteConditionalMenuRequest();
        $request->setAccount($account);
        $request->setMenuId($menuId);

        try {
            $this->officialAccountClient->request($request);

            $this->logger->info('成功删除个性化菜单', [
                'account' => $account->getId(),
                'menuId' => $menuId,
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('删除个性化菜单失败', [
                'account' => $account->getId(),
                'menuId' => $menuId,
                'error' => $e->getMessage(),
            ]);

            throw new WechatApiException(sprintf('删除个性化菜单失败: %s', $e->getMessage()), 0, $e);
        }
    }

    /**
     * 测试个性化菜单匹配.
     *
     * @see https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Personalized_menu_interface.html
     *
     * @return array<string, mixed>
     */
    public function tryMatchMenu(Account $account, string $userId): array
    {
        $request = new TryMatchMenuRequest($userId);
        $request->setAccount($account);

        try {
            $result = $this->officialAccountClient->request($request);
            /** @var array<string, mixed> $result */
            assert(is_array($result));

            return $result;
        } catch (\Throwable $e) {
            $this->logger->error('测试个性化菜单匹配失败', [
                'account' => $account->getId(),
                'userId' => $userId,
                'error' => $e->getMessage(),
            ]);

            throw new WechatApiException(sprintf('测试菜单匹配失败: %s', $e->getMessage()), 0, $e);
        }
    }

    /**
     * 获取自定义菜单配置.
     *
     * @see https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Getting_Custom_Menu_Configurations.html
     *
     * @return array<string, mixed>
     */
    public function getCurrentSelfMenuInfo(Account $account): array
    {
        $request = new GetCurrentSelfMenuInfoRequest();
        $request->setAccount($account);

        try {
            $result = $this->officialAccountClient->request($request);
            /** @var array<string, mixed> $result */
            assert(is_array($result));

            return $result;
        } catch (\Throwable $e) {
            $this->logger->error('获取自定义菜单配置失败', [
                'account' => $account->getId(),
                'error' => $e->getMessage(),
            ]);

            throw new WechatApiException(sprintf('获取菜单配置失败: %s', $e->getMessage()), 0, $e);
        }
    }
}
