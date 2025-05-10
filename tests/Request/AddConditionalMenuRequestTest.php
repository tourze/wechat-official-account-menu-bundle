<?php

namespace WechatOfficialAccountMenuBundle\Tests\Request;

use PHPUnit\Framework\TestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Request\AddConditionalMenuRequest;

class AddConditionalMenuRequestTest extends TestCase
{
    private AddConditionalMenuRequest $request;
    private Account $account;

    protected function setUp(): void
    {
        $this->request = new AddConditionalMenuRequest();
        
        $this->account = $this->createMock(Account::class);
        $this->account->method('getAccessToken')->willReturn('test_access_token');
    }

    public function testGetRequestPath_shouldReturnCorrectUrl(): void
    {
        $expected = 'https://api.weixin.qq.com/cgi-bin/menu/addconditional';
        $this->assertEquals($expected, $this->request->getRequestPath());
    }

    public function testGetRequestOptions_shouldReturnCorrectStructure(): void
    {
        $buttons = [
            [
                'name' => '测试菜单',
                'type' => 'view',
                'url' => 'https://example.com',
            ],
        ];
        
        $matchRule = [
            'tag_id' => '123456',
            'sex' => '1',
            'country' => 'China',
            'province' => 'Guangdong',
            'city' => 'Shenzhen',
        ];
        
        $this->request->setButtons($buttons);
        $this->request->setMatchRule($matchRule);
        
        $options = $this->request->getRequestOptions();
        
        $this->assertIsArray($options);
        $this->assertArrayHasKey('json', $options);
        $this->assertArrayHasKey('button', $options['json']);
        $this->assertArrayHasKey('matchrule', $options['json']);
        $this->assertEquals($buttons, $options['json']['button']);
        $this->assertEquals($matchRule, $options['json']['matchrule']);
    }

    public function testSetGetButtons_shouldWorkCorrectly(): void
    {
        $buttons = [
            [
                'name' => '测试菜单',
                'type' => 'view',
                'url' => 'https://example.com',
            ],
        ];
        
        $this->request->setButtons($buttons);
        
        $this->assertEquals($buttons, $this->request->getButtons());
    }

    public function testSetGetMatchRule_shouldWorkCorrectly(): void
    {
        $matchRule = [
            'tag_id' => '123456',
            'sex' => '1',
            'country' => 'China',
            'province' => 'Guangdong',
            'city' => 'Shenzhen',
        ];
        
        $this->request->setMatchRule($matchRule);
        
        $this->assertEquals($matchRule, $this->request->getMatchRule());
    }

    public function testAccount_shouldBeSettable(): void
    {
        $this->request->setAccount($this->account);
        $this->assertSame($this->account, $this->request->getAccount());
    }
} 