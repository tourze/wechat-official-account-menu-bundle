<?php

namespace WechatOfficialAccountMenuBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use WechatOfficialAccountMenuBundle\Exception\WechatApiException;

/**
 * @internal
 */
#[CoversClass(WechatApiException::class)]
final class WechatApiExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $message = 'Test API error';
        $errorCode = 40001;
        $exception = new WechatApiException($message, $errorCode);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($errorCode, $exception->getErrorCode());
    }
}
