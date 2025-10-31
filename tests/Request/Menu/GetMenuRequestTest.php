<?php

namespace WechatOfficialAccountMenuBundle\Tests\Request\Menu;

use HttpClientBundle\Tests\Request\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use WechatOfficialAccountMenuBundle\Request\Menu\GetMenuRequest;

/**
 * @internal
 */
#[CoversClass(GetMenuRequest::class)]
final class GetMenuRequestTest extends RequestTestCase
{
    public function testGetApiName(): void
    {
        $request = new GetMenuRequest();
        self::assertSame('cgi-bin/get_current_selfmenu_info', $request->getApiName());
    }

    public function testGetHttpMethod(): void
    {
        $request = new GetMenuRequest();
        self::assertSame('GET', $request->getHttpMethod());
    }

    public function testGetRequestData(): void
    {
        $request = new GetMenuRequest();
        self::assertSame([], $request->getRequestData());
    }
}
