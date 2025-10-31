<?php

namespace WechatOfficialAccountMenuBundle\DTO;

use WechatOfficialAccountMenuBundle\Entity\MenuVersion;

class MenuVersionListDTO implements \JsonSerializable
{
    private function __construct(
        private readonly string $id,
        private readonly string $version,
        private readonly ?string $description,
        private readonly string $status,
        private readonly string $statusLabel,
        private readonly \DateTimeInterface $createTime,
        private readonly ?\DateTimeInterface $publishedAt,
        private readonly ?string $publishedBy,
        private readonly ?string $copiedFromVersion,
        private readonly int $buttonCount,
    ) {
    }

    public static function fromMenuVersion(MenuVersion $version): self
    {
        return new self(
            id: $version->getId() ?? '',
            version: $version->getVersion() ?? '',
            description: $version->getDescription(),
            status: $version->getStatus()->value,
            statusLabel: $version->getStatus()->getLabel(),
            createTime: $version->getCreateTime() ?? new \DateTimeImmutable(),
            publishedAt: $version->getPublishedAt(),
            publishedBy: $version->getPublishedBy(),
            copiedFromVersion: $version->getCopiedFrom()?->getVersion(),
            buttonCount: $version->getButtons()->count(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'version' => $this->version,
            'description' => $this->description,
            'status' => $this->status,
            'statusLabel' => $this->statusLabel,
            'createTime' => $this->createTime->format('Y-m-d H:i:s'),
            'publishedAt' => $this->publishedAt?->format('Y-m-d H:i:s'),
            'publishedBy' => $this->publishedBy,
            'copiedFromVersion' => $this->copiedFromVersion,
            'buttonCount' => $this->buttonCount,
        ];
    }

    // Getters
    public function getId(): string
    {
        return $this->id;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getStatusLabel(): string
    {
        return $this->statusLabel;
    }

    public function getCreateTime(): \DateTimeInterface
    {
        return $this->createTime;
    }

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function getPublishedBy(): ?string
    {
        return $this->publishedBy;
    }

    public function getCopiedFromVersion(): ?string
    {
        return $this->copiedFromVersion;
    }

    public function getButtonCount(): int
    {
        return $this->buttonCount;
    }
}
