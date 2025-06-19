<?php

namespace WechatOfficialAccountMenuBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\EnumExtra\Itemable;
use Tourze\JsonRPC\Core\Exception\ApiException;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Enum\MenuType;
use WechatOfficialAccountMenuBundle\Repository\MenuButtonRepository;

/**
 * @see https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Creating_Custom-Defined_Menu.html
 */
#[ORM\Table(name: 'wechat_official_account_menu_button', options: ['comment' => '自定义菜单'])]
#[ORM\Entity(repositoryClass: MenuButtonRepository::class)]
class MenuButton implements \Stringable, Itemable
{
    use TimestampableAware;
    use BlameableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Account $account;

    #[ORM\Column(type: Types::STRING, length: 40, enumType: MenuType::class, options: ['comment' => '响应动作类型'])]
    private ?MenuType $type = null;

    #[ORM\Column(type: Types::STRING, length: 60, options: ['comment' => '菜单标题'])]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: MenuButton::class, inversedBy: 'children')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?MenuButton $parent = null;

    /**
     * 下级分类列表.
     *
     * @var Collection<\WechatOfficialAccountMenuBundle\Entity\MenuButton>
     */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: MenuButton::class)]
    private Collection $children;

    #[ORM\Column(type: Types::STRING, length: 128, nullable: true, options: ['comment' => '菜单KEY值'])]
    private ?string $clickKey = null;

    #[ORM\Column(type: Types::STRING, length: 1024, nullable: true, options: ['comment' => '网页链接'])]
    private ?string $url = null;

    #[ORM\Column(type: Types::STRING, length: 120, nullable: true, options: ['comment' => '小程序AppID'])]
    private ?string $appId = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '小程序页面路径'])]
    private ?string $pagePath = null;


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

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): static
    {
        $this->account = $account;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * 兼容diy-tree的格式要求
     */
    public function getTitle(): string
    {
        return $this->getName();
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<MenuButton>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(self $child): self
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    public function toSelectItem(): array
    {
        return [
            'label' => $this->getName(),
            'text' => $this->getName(),
            'value' => $this->getId(),
        ];
    }

    public function getType(): ?MenuType
    {
        return $this->type;
    }

    public function setType(MenuType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getClickKey(): ?string
    {
        return $this->clickKey;
    }

    public function setClickKey(?string $clickKey): self
    {
        $this->clickKey = $clickKey;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getAppId(): ?string
    {
        return $this->appId;
    }

    public function setAppId(?string $appId): self
    {
        $this->appId = $appId;

        return $this;
    }

    public function getPagePath(): ?string
    {
        return $this->pagePath;
    }

    public function setPagePath(?string $pagePath): self
    {
        $this->pagePath = $pagePath;

        return $this;
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
            throw new ApiException('请选择跟上级同样的公众号');
        }
    }

    /**
     * 格式化为微信需要的格式.
     *
     * @see https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Creating_Custom-Defined_Menu.html
     */
    public function toWechatFormat(): array
    {
        // 如果有下级，那么当前这个菜单就不处理的啦
        if ($this->getChildren()->count() > 0) {
            $result = [
                'name' => $this->getName(),
                'sub_button' => [],
            ];
            foreach ($this->getChildren() as $child) {
                $result['sub_button'][] = $child->toWechatFormat();
            }
        } else {
            $result = [
                'type' => $this->getType()->value,
                'name' => $this->getName(),
            ];
            if (in_array($this->getType(), [
                MenuType::CLICK,
                MenuType::SCAN_CODE_PUSH,
                MenuType::SCAN_CODE_WAIT_MSG,
                MenuType::PIC_SYS_PHOTO,
                MenuType::PIC_PHOTO_ALBUM,
                MenuType::PIC_WEIXIN,
                MenuType::LOCATION_SELECT,
            ])) {
                $result['key'] = $this->getClickKey();
            }

            if (MenuType::VIEW === $this->getType()) {
                $result['url'] = $this->getUrl();
            }

            if (MenuType::MINI_PROGRAM === $this->getType()) {
                $result['url'] = $this->getUrl();
                $result['appid'] = $this->getAppId();
                $result['pagepath'] = $this->getPagePath();
            }

            // TODO 图文
        }

        return $result;
    }
}
