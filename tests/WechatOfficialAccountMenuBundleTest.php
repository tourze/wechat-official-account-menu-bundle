<?php

declare(strict_types=1);

namespace WechatOfficialAccountMenuBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use WechatOfficialAccountMenuBundle\WechatOfficialAccountMenuBundle;

/**
 * @internal
 */
#[CoversClass(WechatOfficialAccountMenuBundle::class)]
#[RunTestsInSeparateProcesses]
final class WechatOfficialAccountMenuBundleTest extends AbstractBundleTestCase
{
}
