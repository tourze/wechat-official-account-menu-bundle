<?php

namespace WechatOfficialAccountMenuBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use WechatOfficialAccountMenuBundle\Exception\MenuValidationException;

/**
 * @internal
 */
#[CoversClass(MenuValidationException::class)]
final class MenuValidationExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $message = 'Test validation error';
        $exception = new MenuValidationException($message);

        $this->assertSame($message, $exception->getMessage());
    }
}
