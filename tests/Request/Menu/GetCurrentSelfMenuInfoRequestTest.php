<?php

namespace WechatOfficialAccountMenuBundle\Tests\Request\Menu;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use WechatOfficialAccountMenuBundle\Request\Menu\GetCurrentSelfMenuInfoRequest;

/**
 * @internal
 */
#[CoversClass(GetCurrentSelfMenuInfoRequest::class)]
final class GetCurrentSelfMenuInfoRequestTest extends RequestTestCase
{
    private GetCurrentSelfMenuInfoRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new GetCurrentSelfMenuInfoRequest();
    }

    public function testGetRequestPath(): void
    {
        self::assertSame('https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info', $this->request->getRequestPath());
    }

    public function testGetRequestMethod(): void
    {
        self::assertSame('GET', $this->request->getRequestMethod());
    }

    public function testGetRequestOptions(): void
    {
        $options = $this->request->getRequestOptions();
        self::assertNull($options);
    }
}
