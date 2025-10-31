<?php

namespace WechatOfficialAccountMenuBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Entity\MenuButton;
use WechatOfficialAccountMenuBundle\Entity\MenuButtonVersion;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;
use WechatOfficialAccountMenuBundle\Enum\MenuType;
use WechatOfficialAccountMenuBundle\Repository\MenuButtonRepository;
use WechatOfficialAccountMenuBundle\Service\MenuButtonCopyService;

/**
 * @internal
 */
#[CoversClass(MenuButtonCopyService::class)]
final class MenuButtonCopyServiceTest extends TestCase
{
    private MockObject $entityManager;

    private MockObject $menuButtonRepository;

    private MenuButtonCopyService $service;

    protected function setUp(): void
    {
        /** @phpstan-ignore-next-line */
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        /** @phpstan-ignore-next-line */
        $this->menuButtonRepository = $this->createMock(MenuButtonRepository::class);
        $this->service = new MenuButtonCopyService(
            $this->entityManager,
            $this->menuButtonRepository
        );
    }

    public function testCopyButtonsFromCurrent(): void
    {
        $account = new Account();
        $version = new MenuVersion();

        $button = new MenuButton();
        $button->setId('1');
        $button->setName('Test Button');
        $button->setType(MenuType::CLICK);

        $this->menuButtonRepository
            ->expects($this->once())
            ->method('findAllByAccount')
            ->with($account)
            ->willReturn([$button])
        ;

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with(self::isInstanceOf(MenuButtonVersion::class))
        ;

        $this->service->copyButtonsFromCurrent($account, $version);
    }

    public function testCopyButtonsFromVersion(): void
    {
        $sourceVersion = new MenuVersion();
        $targetVersion = new MenuVersion();

        $buttonVersion = new MenuButtonVersion();
        $buttonVersion->setName('Test Button');
        $buttonVersion->setType(MenuType::CLICK);

        $sourceVersion->addButton($buttonVersion);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with(self::isInstanceOf(MenuButtonVersion::class))
        ;

        $this->service->copyButtonsFromVersion($sourceVersion, $targetVersion);
    }

    public function testCopyButtonVersion(): void
    {
        $sourceButton = new MenuButtonVersion();
        $sourceButton->setName('Source Button');
        $sourceButton->setType(MenuType::CLICK);
        $sourceButton->setClickKey('test_key');
        $sourceButton->setPosition(1);
        $sourceButton->setEnabled(true);

        $targetVersion = new MenuVersion();

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with(self::isInstanceOf(MenuButtonVersion::class))
        ;

        $result = $this->service->copyButtonVersion($sourceButton, $targetVersion);

        $this->assertInstanceOf(MenuButtonVersion::class, $result);
        $this->assertSame($targetVersion, $result->getVersion());
        $this->assertSame('Source Button', $result->getName());
        $this->assertSame(MenuType::CLICK, $result->getType());
        $this->assertSame('test_key', $result->getClickKey());
        $this->assertSame(1, $result->getPosition());
        $this->assertTrue($result->isEnabled());
    }
}
