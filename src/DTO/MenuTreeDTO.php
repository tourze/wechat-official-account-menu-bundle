<?php

namespace WechatOfficialAccountMenuBundle\DTO;

use WechatOfficialAccountMenuBundle\Entity\MenuButton;
use WechatOfficialAccountMenuBundle\Entity\MenuButtonVersion;

class MenuTreeDTO implements \JsonSerializable
{
    private string $id;

    private string $name;

    private ?string $type;

    private ?string $typeLabel;

    private int $position;

    private bool $enabled;

    private ?string $clickKey;

    private ?string $url;

    private ?string $appId;

    private ?string $pagePath;

    /** @var array<int, MenuTreeDTO> */
    private array $children = [];

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /** @var array<string, mixed> */
    public array $data = [];

    public static function fromMenuButton(MenuButton $button): self
    {
        $dto = new self();
        $dto->id = $button->getId() ?? '';
        $dto->name = $button->getName() ?? '';
        $dto->type = $button->getType()?->value;
        $dto->typeLabel = $button->getType()?->getLabel();
        $dto->position = $button->getPosition();
        $dto->enabled = $button->isEnabled();
        $dto->clickKey = $button->getClickKey();
        $dto->url = $button->getUrl();
        $dto->appId = $button->getAppId();
        $dto->pagePath = $button->getPagePath();

        foreach ($button->getChildren() as $child) {
            $dto->children[] = self::fromMenuButton($child);
        }

        return $dto;
    }

    public static function fromMenuButtonVersion(MenuButtonVersion $button): self
    {
        $dto = new self();
        $dto->id = $button->getId() ?? '';
        $dto->name = $button->getName() ?? '';
        $dto->type = $button->getType()?->value;
        $dto->typeLabel = $button->getType()?->getLabel();
        $dto->position = $button->getPosition();
        $dto->enabled = $button->isEnabled();
        $dto->clickKey = $button->getClickKey();
        $dto->url = $button->getUrl();
        $dto->appId = $button->getAppId();
        $dto->pagePath = $button->getPagePath();

        foreach ($button->getChildren() as $child) {
            $dto->children[] = self::fromMenuButtonVersion($child);
        }

        return $dto;
    }

    /**
     * @return array{id: string, name: string, type: string|null, typeLabel: string|null, position: int, enabled: bool, clickKey: string|null, url: string|null, appId: string|null, pagePath: string|null, children: array<int, MenuTreeDTO>}
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'typeLabel' => $this->typeLabel,
            'position' => $this->position,
            'enabled' => $this->enabled,
            'clickKey' => $this->clickKey,
            'url' => $this->url,
            'appId' => $this->appId,
            'pagePath' => $this->pagePath,
            'children' => $this->children,
        ];
    }

    // Getters
    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getTypeLabel(): ?string
    {
        return $this->typeLabel;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getClickKey(): ?string
    {
        return $this->clickKey;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getAppId(): ?string
    {
        return $this->appId;
    }

    public function getPagePath(): ?string
    {
        return $this->pagePath;
    }

    /**
     * @return array<int, MenuTreeDTO>
     */
    public function getChildren(): array
    {
        return $this->children;
    }
}
