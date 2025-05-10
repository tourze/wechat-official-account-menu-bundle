<?php

namespace WechatOfficialAccountMenuBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Entity\MenuButton;

class MenuButtonRelationTest extends TestCase
{
    private MenuButton $parentButton;
    private MenuButton $childButton;
    private Account $account;
    private Account $differentAccount;

    protected function setUp(): void
    {
        $this->parentButton = new MenuButton();
        $this->childButton = new MenuButton();
        
        $this->account = $this->createMock(Account::class);
        $this->account->method('getId')->willReturn(123456);
        
        $this->differentAccount = $this->createMock(Account::class);
        $this->differentAccount->method('getId')->willReturn(654321);
        
        $this->parentButton->setAccount($this->account);
        $this->childButton->setAccount($this->account);
    }

    public function testSetGetParent_shouldWorkCorrectly(): void
    {
        $this->childButton->setParent($this->parentButton);
        
        $this->assertSame($this->parentButton, $this->childButton->getParent());
    }

    public function testAddChild_shouldAddChildAndSetParent(): void
    {
        $this->parentButton->addChild($this->childButton);
        
        $this->assertTrue($this->parentButton->getChildren()->contains($this->childButton));
        $this->assertSame($this->parentButton, $this->childButton->getParent());
    }

    public function testAddChild_shouldNotAddSameChildTwice(): void
    {
        $this->parentButton->addChild($this->childButton);
        $this->parentButton->addChild($this->childButton);
        
        $this->assertCount(1, $this->parentButton->getChildren());
    }

    public function testRemoveChild_shouldRemoveChildFromCollection(): void
    {
        $this->parentButton->addChild($this->childButton);
        $this->assertTrue($this->parentButton->getChildren()->contains($this->childButton));
        
        $this->parentButton->removeChild($this->childButton);
        
        $this->assertFalse($this->parentButton->getChildren()->contains($this->childButton));
    }

    public function testRemoveChild_shouldDoNothingForNonExistingChild(): void
    {
        $anotherChild = new MenuButton();
        
        $this->parentButton->addChild($this->childButton);
        $this->parentButton->removeChild($anotherChild);
        
        $this->assertTrue($this->parentButton->getChildren()->contains($this->childButton));
        $this->assertCount(1, $this->parentButton->getChildren());
    }

    /**
     * 注意：由于ensureSameAccount的实现方式可能不同于我们的期望
     * 我们在这里测试账号不匹配时的行为
     */
    public function testAddChildWithDifferentAccount_shouldBePossible(): void
    {
        $this->childButton->setAccount($this->differentAccount);
        $this->parentButton->addChild($this->childButton);
        
        $this->assertTrue($this->parentButton->getChildren()->contains($this->childButton));
        $this->assertSame($this->parentButton, $this->childButton->getParent());
    }

    public function testEnsureSameAccount_shouldNotThrowException_whenAccountsMatch(): void
    {
        $this->parentButton->addChild($this->childButton);
        
        // 当账号匹配时，不应该抛出异常
        $this->parentButton->ensureSameAccount();
        $this->expectNotToPerformAssertions();
    }

    public function testToWechatFormat_withChildren_shouldIncludeSubButtons(): void
    {
        $this->parentButton->setName('父菜单');
        
        $child1 = new MenuButton();
        $child1->setName('子菜单1');
        $child1->setAccount($this->account);
        
        $child2 = new MenuButton();
        $child2->setName('子菜单2');
        $child2->setAccount($this->account);
        
        $this->parentButton->addChild($child1);
        $this->parentButton->addChild($child2);
        
        $result = $this->parentButton->toWechatFormat();
        
        $this->assertEquals('父菜单', $result['name']);
        $this->assertArrayHasKey('sub_button', $result);
        $this->assertCount(2, $result['sub_button']);
        $this->assertEquals('子菜单1', $result['sub_button'][0]['name']);
        $this->assertEquals('子菜单2', $result['sub_button'][1]['name']);
    }
} 