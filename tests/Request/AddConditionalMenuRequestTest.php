<?php

namespace WechatOfficialAccountMenuBundle\Tests\Request;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Request\Menu\AddConditionalMenuRequest;

/**
 * @internal
 */
#[CoversClass(AddConditionalMenuRequest::class)]
final class AddConditionalMenuRequestTest extends RequestTestCase
{
    private AddConditionalMenuRequest $request;

    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new AddConditionalMenuRequest();
        $this->account = new Account();
    }

    public function testGetRequestPathShouldReturnCorrectUrl(): void
    {
        $expected = 'https://api.weixin.qq.com/cgi-bin/menu/addconditional';
        $this->assertEquals($expected, $this->request->getRequestPath());
    }

    public function testGetRequestOptionsShouldReturnCorrectStructure(): void
    {
        $menuData = [
            'button' => [
                [
                    'name' => '测试菜单',
                    'type' => 'view',
                    'url' => 'https://example.com',
                ],
            ],
        ];

        $matchRule = [
            'tag_id' => '123456',
            'sex' => '1',
            'country' => 'China',
            'province' => 'Guangdong',
            'city' => 'Shenzhen',
        ];

        $this->request->setMenuData($menuData);
        $this->request->setMatchRule($matchRule);

        $options = $this->request->getRequestOptions();
        $this->assertIsArray($options);
        $this->assertArrayHasKey('json', $options);
        $this->assertIsArray($options['json']);
        $this->assertArrayHasKey('button', $options['json']);
        $this->assertArrayHasKey('matchrule', $options['json']);
        $this->assertEquals($menuData['button'], $options['json']['button']);
        $this->assertEquals($matchRule, $options['json']['matchrule']);
    }

    public function testSetGetMenuDataShouldWorkCorrectly(): void
    {
        $menuData = [
            'button' => [
                [
                    'name' => '测试菜单',
                    'type' => 'view',
                    'url' => 'https://example.com',
                ],
            ],
        ];

        $this->request->setMenuData($menuData);

        $this->assertEquals($menuData, $this->request->getMenuData());
    }

    public function testSetGetMatchRuleShouldWorkCorrectly(): void
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

    public function testAccountShouldBeSettable(): void
    {
        $this->request->setAccount($this->account);
        $this->assertSame($this->account, $this->request->getAccount());
    }
}
