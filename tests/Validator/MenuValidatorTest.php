<?php

namespace WechatOfficialAccountMenuBundle\Tests\Validator;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WechatOfficialAccountMenuBundle\Entity\MenuButton;
use WechatOfficialAccountMenuBundle\Entity\MenuButtonVersion;
use WechatOfficialAccountMenuBundle\Enum\MenuType;
use WechatOfficialAccountMenuBundle\Exception\MenuValidationException;
use WechatOfficialAccountMenuBundle\Validator\MenuValidator;

/**
 * @internal
 */
#[CoversClass(MenuValidator::class)]
final class MenuValidatorTest extends TestCase
{
    private MenuValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new MenuValidator();
    }

    public function testValidatorCanBeInstantiated(): void
    {
        $this->assertInstanceOf(MenuValidator::class, $this->validator);
    }

    public function testValidateMenuButtonWithValidButton(): void
    {
        $button = new class extends MenuButton {
            public function getName(): string
            {
                return 'Test Button';
            }

            public function getType(): MenuType
            {
                return MenuType::CLICK;
            }

            public function getParent(): ?MenuButton
            {
                return null;
            }

            public function getClickKey(): string
            {
                return 'test_key';
            }

            public function getChildren(): Collection
            {
                return new ArrayCollection();
            }
        };

        // 验证不会抛出异常
        $this->expectNotToPerformAssertions();
        $this->validator->validateMenuButton($button);
    }

    public function testValidateMenuButtonWithEmptyName(): void
    {
        $button = new class extends MenuButton {
            public function getName(): string
            {
                return '';
            }

            public function getType(): MenuType
            {
                return MenuType::CLICK;
            }

            public function getParent(): ?MenuButton
            {
                return null;
            }

            public function getChildren(): Collection
            {
                return new ArrayCollection();
            }
        };

        $this->expectException(MenuValidationException::class);
        $this->expectExceptionMessage('菜单名称不能为空');

        $this->validator->validateMenuButton($button);
    }

    public function testValidateMenuButtonVersion(): void
    {
        $button = new class extends MenuButtonVersion {
            public function getName(): string
            {
                return 'Test Button Version';
            }

            public function getType(): MenuType
            {
                return MenuType::CLICK;
            }

            public function getParent(): ?MenuButtonVersion
            {
                return null;
            }

            public function getClickKey(): string
            {
                return 'test_key';
            }
        };

        // 验证不会抛出异常
        $this->expectNotToPerformAssertions();
        $this->validator->validateMenuButtonVersion($button);
    }

    public function testValidateMenuStructure(): void
    {
        $rootButton1 = new class extends MenuButton {
            public function getName(): string
            {
                return 'Root Button 1';
            }

            public function getChildren(): Collection
            {
                return new ArrayCollection();
            }
        };

        $rootButton2 = new class extends MenuButton {
            public function getName(): string
            {
                return 'Root Button 2';
            }

            public function getChildren(): Collection
            {
                return new ArrayCollection();
            }
        };

        $rootButtons = [$rootButton1, $rootButton2];
        $errors = $this->validator->validateMenuStructure($rootButtons);

        $this->assertEmpty($errors);
    }

    public function testValidateMenuButtonWithInvalidViewType(): void
    {
        $button = new class extends MenuButton {
            public function getName(): string
            {
                return 'View Button';
            }

            public function getType(): MenuType
            {
                return MenuType::VIEW;
            }

            public function getParent(): ?MenuButton
            {
                return null;
            }

            public function getUrl(): string
            {
                return ''; // 空URL应该抛出异常
            }

            public function getChildren(): Collection
            {
                return new ArrayCollection();
            }
        };

        $this->expectException(MenuValidationException::class);
        $this->expectExceptionMessage('跳转URL类型的菜单必须设置URL');

        $this->validator->validateMenuButton($button);
    }

    public function testValidateMenuButtonVersionWithMissingClickKey(): void
    {
        $button = new class extends MenuButtonVersion {
            public function getName(): string
            {
                return 'Click Button Version';
            }

            public function getType(): MenuType
            {
                return MenuType::CLICK;
            }

            public function getParent(): ?MenuButtonVersion
            {
                return null;
            }

            public function getClickKey(): string
            {
                return ''; // 空key应该抛出异常
            }
        };

        $this->expectException(MenuValidationException::class);
        $this->expectExceptionMessage('类型为"点击推事件"的菜单必须设置Key值');

        $this->validator->validateMenuButtonVersion($button);
    }

    public function testValidateMenuStructureWithTooManyRootButtons(): void
    {
        $rootButtons = [];
        for ($i = 1; $i <= 4; ++$i) {
            $rootButtons[] = new class($i) extends MenuButton {
                private int $buttonNumber;

                public function __construct(int $buttonNumber)
                {
                    parent::__construct();
                    $this->buttonNumber = $buttonNumber;
                }

                public function getName(): string
                {
                    return "Root Button {$this->buttonNumber}";
                }

                public function getChildren(): Collection
                {
                    return new ArrayCollection();
                }
            };
        }

        $errors = $this->validator->validateMenuStructure($rootButtons);

        $this->assertCount(1, $errors);
        $this->assertStringContainsString('一级菜单最多3个，当前有4个', $errors[0]);
    }
}
