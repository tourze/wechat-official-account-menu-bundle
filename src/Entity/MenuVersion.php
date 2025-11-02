<?php

namespace WechatOfficialAccountMenuBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Enum\MenuVersionStatus;
use WechatOfficialAccountMenuBundle\Repository\MenuVersionRepository;

#[ORM\Table(name: 'wechat_official_account_menu_version', options: ['comment' => '菜单版本'])]
#[ORM\Entity(repositoryClass: MenuVersionRepository::class)]
#[ORM\Index(columns: ['account_id', 'status'], name: 'wechat_official_account_menu_version_idx_account_status')]
class MenuVersion implements \Stringable
{
    use SnowflakeKeyAware;
    use TimestampableAware;
    use BlameableAware;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Account $account;

    #[ORM\Column(type: Types::STRING, length: 20, options: ['comment' => '版本号'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    private ?string $version = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '版本描述'])]
    #[Assert\Length(max: 255)]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: MenuVersionStatus::class, options: ['comment' => '状态'])]
    #[Assert\NotNull]
    #[Assert\Choice(callback: [MenuVersionStatus::class, 'cases'])]
    private MenuVersionStatus $status = MenuVersionStatus::DRAFT;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '发布时间'])]
    #[Assert\Type(type: \DateTimeImmutable::class)]
    #[IndexColumn]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '发布人'])]
    #[Assert\Length(max: 255)]
    private ?string $publishedBy = null;

    #[ORM\ManyToOne(targetEntity: MenuVersion::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?MenuVersion $copiedFrom = null;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '菜单快照'])]
    #[Assert\Type(type: 'array')]
    private ?array $menuSnapshot = null;

    /**
     * @var Collection<int, MenuButtonVersion>
     */
    #[ORM\OneToMany(mappedBy: 'version', targetEntity: MenuButtonVersion::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(value: ['position' => 'ASC', 'id' => 'ASC'])]
    private Collection $buttons;

    public function __construct()
    {
        $this->buttons = new ArrayCollection();
    }

    public function __toString(): string
    {
        return "v{$this->getVersion()} - {$this->getStatus()->getLabel()}";
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): void
    {
        $this->account = $account;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getStatus(): MenuVersionStatus
    {
        return $this->status;
    }

    public function setStatus(MenuVersionStatus $status): void
    {
        $this->status = $status;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeImmutable $publishedAt): void
    {
        $this->publishedAt = $publishedAt;
    }

    public function getPublishedBy(): ?string
    {
        return $this->publishedBy;
    }

    public function setPublishedBy(?string $publishedBy): void
    {
        $this->publishedBy = $publishedBy;
    }

    public function getCopiedFrom(): ?self
    {
        return $this->copiedFrom;
    }

    public function setCopiedFrom(?self $copiedFrom): void
    {
        $this->copiedFrom = $copiedFrom;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getMenuSnapshot(): ?array
    {
        return $this->menuSnapshot;
    }

    /**
     * @param array<string, mixed>|null $menuSnapshot
     */
    public function setMenuSnapshot(?array $menuSnapshot): void
    {
        $this->menuSnapshot = $menuSnapshot;
    }

    /**
     * @return Collection<int, MenuButtonVersion>
     */
    public function getButtons(): Collection
    {
        return $this->buttons;
    }

    public function addButton(MenuButtonVersion $button): void
    {
        if (!$this->buttons->contains($button)) {
            $this->buttons->add($button);
            $button->setVersion($this);
        }
    }

    public function removeButton(MenuButtonVersion $button): void
    {
        if ($this->buttons->removeElement($button)) {
            if ($button->getVersion() === $this) {
                $button->setVersion(null);
            }
        }
    }

    /**
     * 获取一级菜单.
     *
     * @return Collection<int, MenuButtonVersion>
     */
    public function getRootButtons(): Collection
    {
        return $this->buttons->filter(fn (MenuButtonVersion $button) => null === $button->getParent());
    }

    /**
     * 获取菜单按钮数量.
     */
    public function getButtonsCount(): int
    {
        return $this->buttons->count();
    }

    /**
     * 是否为草稿状态.
     */
    public function isDraft(): bool
    {
        return MenuVersionStatus::DRAFT === $this->status;
    }

    /**
     * 是否为已发布状态.
     */
    public function isPublished(): bool
    {
        return MenuVersionStatus::PUBLISHED === $this->status;
    }

    /**
     * 是否为已归档状态.
     */
    public function isArchived(): bool
    {
        return MenuVersionStatus::ARCHIVED === $this->status;
    }

    /**
     * 发布版本.
     */
    public function publish(string $publishedBy): void
    {
        $this->status = MenuVersionStatus::PUBLISHED;
        $this->publishedAt = new \DateTimeImmutable();
        $this->publishedBy = $publishedBy;
    }

    /**
     * 归档版本.
     */
    public function archive(): void
    {
        $this->status = MenuVersionStatus::ARCHIVED;
    }

    /**
     * 生成微信格式的菜单数据.
     *
     * @return array<string, mixed>
     */
    public function toWechatFormat(): array
    {
        $buttons = [];
        foreach ($this->getRootButtons() as $button) {
            $buttons[] = $button->toWechatFormat();
        }

        return [
            'button' => $buttons,
        ];
    }

    /**
     * 生成下一个版本号.
     */
    public static function generateNextVersion(string $currentVersion): string
    {
        $parts = explode('.', $currentVersion);
        $lastPart = (int) end($parts);
        $parts[count($parts) - 1] = (string) ($lastPart + 1);

        return implode('.', $parts);
    }
}
