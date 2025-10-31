<?php

namespace WechatOfficialAccountMenuBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Entity\MenuButtonVersion;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;
use WechatOfficialAccountMenuBundle\Enum\MenuType;
use WechatOfficialAccountMenuBundle\Enum\MenuVersionStatus;
use WechatOfficialAccountMenuBundle\Event\MenuVersionCreatedEvent;
use WechatOfficialAccountMenuBundle\Event\MenuVersionPublishedEvent;
use WechatOfficialAccountMenuBundle\Exception\MenuVersionException;
use WechatOfficialAccountMenuBundle\Repository\MenuVersionRepository;

#[WithMonologChannel(channel: 'wechat_official_account_menu')]
class MenuVersionService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MenuVersionRepository $menuVersionRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly Security $security,
        private readonly WechatMenuApiService $wechatMenuApiService,
        private readonly LoggerInterface $logger,
        private readonly MenuButtonCopyService $buttonCopyService,
        private readonly MenuVersionComparator $versionComparator,
    ) {
    }

    /**
     * 创建新版本.
     */
    public function createVersion(
        Account $account,
        ?string $description = null,
        ?MenuVersion $copyFrom = null,
    ): MenuVersion {
        $version = new MenuVersion();
        $version->setAccount($account);
        $version->setDescription($description);

        // 生成版本号
        $versionNumber = $this->menuVersionRepository->generateNextVersionNumber($account);
        $version->setVersion($versionNumber);

        if (null !== $copyFrom) {
            $version->setCopiedFrom($copyFrom);
            $this->buttonCopyService->copyButtonsFromVersion($copyFrom, $version);
        } else {
            // 从当前菜单创建版本
            $this->buttonCopyService->copyButtonsFromCurrent($account, $version);
        }

        $this->entityManager->persist($version);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new MenuVersionCreatedEvent($version));

        return $version;
    }

    /**
     * 发布版本.
     */
    public function publishVersion(MenuVersion $version): void
    {
        $this->validateVersionForPublishing($version);
        $menuData = $this->prepareMenuData($version);
        $publishedBy = $this->security->getUser()?->getUserIdentifier() ?? 'system';

        $this->publishToWechat($version, $menuData, $publishedBy);
        $this->updateVersionStatus($version, $publishedBy);
        $this->archiveOldVersions($version);

        $this->entityManager->flush();
        $this->eventDispatcher->dispatch(new MenuVersionPublishedEvent($version));
    }

    /**
     * 回滚到指定版本.
     */
    public function rollbackToVersion(MenuVersion $version): MenuVersion
    {
        if ($version->isDraft()) {
            throw new MenuVersionException('不能回滚到草稿版本');
        }

        // 创建新的草稿版本
        return $this->createVersion(
            $version->getAccount(),
            '回滚至版本 ' . $version->getVersion(),
            $version
        );
    }

    /**
     * 归档版本.
     */
    public function archiveVersion(MenuVersion $version): void
    {
        if ($version->isDraft()) {
            // 草稿直接删除
            $this->entityManager->remove($version);
        } else {
            // 已发布的版本归档
            $version->archive();
        }

        $this->entityManager->flush();
    }

    /**
     * 对比两个版本.
     *
     * @return array{added: array<int, array<string, mixed>>, removed: array<int, array<string, mixed>>, modified: array<int, array<string, mixed>>}
     */
    public function compareVersions(MenuVersion $version1, MenuVersion $version2): array
    {
        return $this->versionComparator->compareVersions($version1, $version2);
    }

    /**
     * 更新版本中的菜单按钮.
     *
     * @param array<string, mixed> $data
     */
    public function updateVersionButton(MenuButtonVersion $button, array $data): void
    {
        $version = $button->getVersion();
        if (null === $version || !$version->isDraft()) {
            throw new MenuVersionException('只能编辑草稿版本的菜单');
        }

        $this->updateButtonFromData($button, $data);
        $this->entityManager->flush();
    }

    /**
     * 验证版本是否可发布
     */
    private function validateVersionForPublishing(MenuVersion $version): void
    {
        if (!$version->isDraft()) {
            throw new MenuVersionException('只能发布草稿版本');
        }

        $errors = $this->validateVersionStructure($version);
        if (count($errors) > 0) {
            throw new MenuVersionException('菜单结构验证失败: ' . implode(', ', $errors));
        }
    }

    /**
     * 准备菜单数据
     *
     * @return array<string, mixed>
     */
    private function prepareMenuData(MenuVersion $version): array
    {
        $menuData = $version->toWechatFormat();
        $version->setMenuSnapshot($menuData);

        return $menuData;
    }

    /**
     * 发布到微信
     *
     * @param array<string, mixed> $menuData
     */
    private function publishToWechat(MenuVersion $version, array $menuData, string $publishedBy): void
    {
        $startTime = microtime(true);

        try {
            $this->logPublishStart($version, $publishedBy, $menuData);
            $this->wechatMenuApiService->createMenu($version->getAccount(), $menuData);
            $this->logPublishSuccess($version, $publishedBy, $startTime);
        } catch (\Throwable $e) {
            $this->logPublishFailure($version, $publishedBy, $startTime, $e);
            throw $e;
        }
    }

    /**
     * 更新版本状态
     */
    private function updateVersionStatus(MenuVersion $version, string $publishedBy): void
    {
        $previousStatus = $version->getStatus();

        $this->logger->info('开始更新菜单版本状态为已发布', [
            'version_id' => $version->getId(),
            'account_id' => $version->getAccount()->getId(),
            'published_by' => $publishedBy,
            'previous_status' => $previousStatus->value,
            'new_status' => MenuVersionStatus::PUBLISHED->value,
        ]);

        $version->publish($publishedBy);

        $this->logger->info('菜单版本状态更新完成', [
            'version_id' => $version->getId(),
            'account_id' => $version->getAccount()->getId(),
            'published_by' => $publishedBy,
            'previous_status' => $previousStatus->value,
            'current_status' => $version->getStatus()->value,
            'published_at' => $version->getPublishedAt()?->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 归档旧版本
     */
    private function archiveOldVersions(MenuVersion $version): void
    {
        $this->menuVersionRepository->archiveOldPublishedVersions($version->getAccount(), $version);
    }

    /**
     * 验证版本菜单结构.
     *
     * @return array<int, string>
     */
    private function validateVersionStructure(MenuVersion $version): array
    {
        $errors = [];
        $rootButtons = $version->getRootButtons();

        // 检查一级菜单数量
        if ($rootButtons->count() > 3) {
            $errors[] = '一级菜单最多3个';
        }

        // 检查每个一级菜单的子菜单数量
        foreach ($rootButtons as $rootButton) {
            if ($rootButton->getChildren()->count() > 5) {
                $errors[] = sprintf(
                    '菜单"%s"的子菜单超过5个',
                    $rootButton->getName()
                );
            }
        }

        return $errors;
    }

    /**
     * 记录发布开始
     *
     * @param array<string, mixed> $menuData
     */
    private function logPublishStart(MenuVersion $version, string $publishedBy, array $menuData): void
    {
        $this->logger->info('开始发布菜单版本到微信', [
            'version_id' => $version->getId(),
            'account_id' => $version->getAccount()->getId(),
            'published_by' => $publishedBy,
            'menu_data' => $menuData,
        ]);
    }

    /**
     * 记录发布成功
     */
    private function logPublishSuccess(MenuVersion $version, string $publishedBy, float $startTime): void
    {
        $duration = round((microtime(true) - $startTime) * 1000, 2);

        $this->logger->info('菜单版本发布成功', [
            'version_id' => $version->getId(),
            'account_id' => $version->getAccount()->getId(),
            'published_by' => $publishedBy,
            'duration_ms' => $duration,
        ]);
    }

    /**
     * 记录发布失败
     */
    private function logPublishFailure(MenuVersion $version, string $publishedBy, float $startTime, \Throwable $e): void
    {
        $duration = round((microtime(true) - $startTime) * 1000, 2);

        $this->logger->error('菜单版本发布失败', [
            'version_id' => $version->getId(),
            'account_id' => $version->getAccount()->getId(),
            'published_by' => $publishedBy,
            'duration_ms' => $duration,
            'error' => $e->getMessage(),
            'exception' => $e::class,
        ]);
    }

    /**
     * 更新按钮数据.
     *
     * @param array<string, mixed> $data
     */
    private function updateButtonFromData(MenuButtonVersion $button, array $data): void
    {
        $this->updateButtonBasicData($button, $data);
        $this->updateButtonTypeSpecificData($button, $data);
        $this->updateButtonMetaData($button, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateButtonBasicData(MenuButtonVersion $button, array $data): void
    {
        if (isset($data['type'])) {
            $type = $data['type'];
            assert($type instanceof MenuType);
            $button->setType($type);
        }
        if (isset($data['name'])) {
            $name = $data['name'];
            assert(is_string($name));
            $button->setName($name);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateButtonTypeSpecificData(MenuButtonVersion $button, array $data): void
    {
        if (isset($data['clickKey'])) {
            /** @var string|null $clickKey */
            $clickKey = $data['clickKey'];
            $button->setClickKey($clickKey);
        }
        if (isset($data['url'])) {
            /** @var string|null $url */
            $url = $data['url'];
            $button->setUrl($url);
        }
        if (isset($data['appId'])) {
            /** @var string|null $appId */
            $appId = $data['appId'];
            $button->setAppId($appId);
        }
        if (isset($data['pagePath'])) {
            /** @var string|null $pagePath */
            $pagePath = $data['pagePath'];
            $button->setPagePath($pagePath);
        }
        if (isset($data['mediaId'])) {
            /** @var string|null $mediaId */
            $mediaId = $data['mediaId'];
            $button->setMediaId($mediaId);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateButtonMetaData(MenuButtonVersion $button, array $data): void
    {
        if (isset($data['position'])) {
            $position = $data['position'];
            assert(is_int($position));
            $button->setPosition($position);
        }
        if (isset($data['enabled'])) {
            $enabled = $data['enabled'];
            assert(is_bool($enabled));
            $button->setEnabled($enabled);
        }
    }
}
