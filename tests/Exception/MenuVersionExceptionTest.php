<?php

namespace WechatOfficialAccountMenuBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use WechatOfficialAccountMenuBundle\Exception\MenuVersionException;

/**
 * @internal
 */
#[CoversClass(MenuVersionException::class)]
final class MenuVersionExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $message = 'Test version error';
        $exception = new MenuVersionException($message);

        $this->assertSame($message, $exception->getMessage());
    }
}
