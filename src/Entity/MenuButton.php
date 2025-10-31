<?php

namespace WechatOfficialAccountMenuBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\EnumExtra\Itemable;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Enum\MenuType;
use WechatOfficialAccountMenuBundle\Exception\MenuValidationException;
use WechatOfficialAccountMenuBundle\Repository\MenuButtonRepository;

/**
 * @see https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Creating_Custom-Defined_Menu.html
 */
#[ORM\Table(name: 'wechat_official_account_menu_button', options: ['comment' => '自定义菜单'])]
#[ORM\Entity(repositoryClass: MenuButtonRepository::class)]
class MenuButton implements \Stringable, Itemable
{
    use SnowflakeKeyAware;
    use TimestampableAware;
    use BlameableAware;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Account $account;

    #[ORM\Column(type: Types::STRING, length: 40, enumType: MenuType::class, options: ['comment' => '响应动作类型'])]
    #[Assert\NotNull]
    #[Assert\Choice(callback: [MenuType::class, 'cases'])]
    private ?MenuType $type = null;

    #[ORM\Column(type: Types::STRING, length: 60, options: ['comment' => '菜单标题'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 60)]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: MenuButton::class, inversedBy: 'children')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?MenuButton $parent = null;

    /**
     * 下级分类列表.
     *
     * @var Collection<int, MenuButton>
     */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: MenuButton::class)]
    private Collection $children;

    #[ORM\Column(type: Types::STRING, length: 128, nullable: true, options: ['comment' => '菜单KEY值'])]
    #[Assert\Length(max: 128)]
    private ?string $clickKey = null;

    #[ORM\Column(type: Types::STRING, length: 1024, nullable: true, options: ['comment' => '网页链接'])]
    #[Assert\Url]
    #[Assert\Length(max: 1024)]
    private ?string $url = null;

    #[ORM\Column(type: Types::STRING, length: 120, nullable: true, options: ['comment' => '小程序AppID'])]
    #[Assert\Length(max: 120)]
    private ?string $appId = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '小程序页面路径'])]
    #[Assert\Length(max: 1000)]
    private ?string $pagePath = null;

    #[ORM\Column(type: Types::STRING, length: 64, nullable: true, options: ['comment' => '媒体文件ID'])]
    #[Assert\Length(max: 64)]
    private ?string $mediaId = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0, 'comment' => '排序位置'])]
    #[Assert\GreaterThanOrEqual(value: 0)]
    private int $position = 0;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true, 'comment' => '是否启用'])]
    #[Assert\Type(type: 'bool')]
    private bool $enabled = true;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function __toString(): string
    {
        if (null === $this->getId()) {
            return '';
        }

        return "#{$this->getId()} {$this->getName()}";
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): void
    {
        $this->account = $account;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * 兼容diy-tree的格式要求
     */
    public function getTitle(): string
    {
        return $this->getName() ?? '';
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return Collection<int, MenuButton>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): void
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }
    }

    public function removeChild(self $child): void
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }
    }

    /**
     * @return array{label: string, text: string, value: string|null}
     */
    public function toSelectItem(): array
    {
        return [
            'label' => $this->getName() ?? '',
            'text' => $this->getName() ?? '',
            'value' => $this->getId(),
        ];
    }

    public function getType(): ?MenuType
    {
        return $this->type;
    }

    public function setType(MenuType $type): void
    {
        $this->type = $type;
    }

    public function getClickKey(): ?string
    {
        return $this->clickKey;
    }

    public function setClickKey(?string $clickKey): void
    {
        $this->clickKey = $clickKey;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function getAppId(): ?string
    {
        return $this->appId;
    }

    public function setAppId(?string $appId): void
    {
        $this->appId = $appId;
    }

    public function getPagePath(): ?string
    {
        return $this->pagePath;
    }

    public function setPagePath(?string $pagePath): void
    {
        $this->pagePath = $pagePath;
    }

    public function getMediaId(): ?string
    {
        return $this->mediaId;
    }

    public function setMediaId(?string $mediaId): void
    {
        $this->mediaId = $mediaId;
    }

    /**
     * 检查上级跟当前记录，属于同一个Account.
     */
    public function ensureSameAccount(): void
    {
        if (null === $this->getParent()) {
            return;
        }

        if ($this->getAccount()->getId() !== $this->getParent()->getAccount()->getId()) {
            throw new MenuValidationException('请选择跟上级同样的公众号');
        }
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * 格式化为微信需要的格式.
     *
     * @see https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Creating_Custom-Defined_Menu.html
     *
     * @return array<string, mixed>
     */
    public function toWechatFormat(): array
    {
        if ($this->hasChildren()) {
            return $this->formatParentMenu();
        }

        return $this->formatLeafMenu();
    }

    private function hasChildren(): bool
    {
        return $this->getChildren()->count() > 0;
    }

    /**
     * @return array<string, mixed>
     */
    private function formatParentMenu(): array
    {
        $result = [
            'name' => $this->getName(),
            'sub_button' => [],
        ];

        foreach ($this->getChildren() as $child) {
            if ($child->isEnabled()) {
                $result['sub_button'][] = $child->toWechatFormat();
            }
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function formatLeafMenu(): array
    {
        $type = $this->getType();
        $result = [
            'type' => $type?->value,
            'name' => $this->getName(),
        ];

        if (null === $type) {
            return $result;
        }

        return $this->addMenuTypeSpecificData($result, $type);
    }

    /**
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    private function addMenuTypeSpecificData(array $result, MenuType $type): array
    {
        if ($this->isClickMenuType($type)) {
            $result['key'] = $this->getClickKey();
        } elseif (MenuType::VIEW === $type) {
            $result['url'] = $this->getUrl();
        } elseif (MenuType::MINI_PROGRAM === $type) {
            $result['url'] = $this->getUrl();
            $result['appid'] = $this->getAppId();
            $result['pagepath'] = $this->getPagePath();
        }

        return $result;
    }

    private function isClickMenuType(MenuType $type): bool
    {
        return in_array($type, [
            MenuType::CLICK,
            MenuType::SCAN_CODE_PUSH,
            MenuType::SCAN_CODE_WAIT_MSG,
            MenuType::PIC_SYS_PHOTO,
            MenuType::PIC_PHOTO_ALBUM,
            MenuType::PIC_WEIXIN,
            MenuType::LOCATION_SELECT,
        ], true);
    }
}
