<?php

namespace WechatOfficialAccountMenuBundle\Tests\Request\Menu;

use HttpClientBundle\Tests\Request\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use WechatOfficialAccountMenuBundle\Request\Menu\TryMatchMenuRequest;

/**
 * @internal
 */
#[CoversClass(TryMatchMenuRequest::class)]
final class TryMatchMenuRequestTest extends RequestTestCase
{
    public function testGetApiName(): void
    {
        $request = new TryMatchMenuRequest('test-user-id');
        self::assertSame('cgi-bin/menu/trymatch', $request->getApiName());
    }

    public function testGetHttpMethod(): void
    {
        $request = new TryMatchMenuRequest('test-user-id');
        self::assertSame('POST', $request->getHttpMethod());
    }

    public function testGetRequestData(): void
    {
        $userId = 'test-user-id';
        $request = new TryMatchMenuRequest($userId);

        $expected = [
            'user_id' => $userId,
        ];

        self::assertSame($expected, $request->getRequestData());
    }

    public function testConstructorSetsUserId(): void
    {
        $userId = 'test-user-id';
        $request = new TryMatchMenuRequest($userId);

        self::assertSame($userId, $request->getUserId());
    }

    public function testSetUserId(): void
    {
        $request = new TryMatchMenuRequest('initial-user-id');
        $newUserId = 'new-user-id';

        $request->setUserId($newUserId);

        self::assertSame($newUserId, $request->getUserId());
    }
}
