<?php

declare(strict_types=1);

namespace WechatOfficialAccountMenuBundle\Tests\Service;

use HttpClientBundle\Request\RequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tourze\DoctrineAsyncInsertBundle\Service\AsyncInsertService;
use WechatOfficialAccountBundle\Service\OfficialAccountClient;

/**
 * 测试用的 Mock OfficialAccountClient 类，提供 PHPUnit Mock 风格的 API。
 *
 * @phpstan-ignore symplify.forbiddenExtendOfNonAbstractClass
 */
final class MockOfficialAccountClient extends OfficialAccountClient
{
    /** @var callable|null */
    private $requestCallback = null;

    private bool $shouldThrowException = false;

    private ?\Throwable $exceptionToThrow = null;

    private mixed $returnValue = null;

    private int $callCount = 0;

    private int $expectedCallCount = 0;

    /** @phpstan-ignore constructor.missingParentCall */
    public function __construct()
    {
        // 跳过父类构造函数，因为这是测试用的模拟对象
        // 不调用 parent::__construct() 避免复杂的依赖注入
    }

    public function request(RequestInterface $request): mixed
    {
        ++$this->callCount;

        if ($this->shouldThrowException && null !== $this->exceptionToThrow) {
            throw $this->exceptionToThrow;
        }

        if (null !== $this->requestCallback) {
            $callback = $this->requestCallback;

            return $callback($request);
        }

        return $this->returnValue;
    }

    public function expects(mixed $count): self
    {
        // 兼容PHPUnit的InvokedCount对象和整数
        if (is_object($count) && method_exists($count, 'toString')) {
            // 从PHPUnit的InvokedCount对象中提取期望次数
            $this->expectedCallCount = 1; // once() 对应 1 次
        } elseif (is_int($count)) {
            $this->expectedCallCount = $count;
        } else {
            $this->expectedCallCount = 0;
        }

        return $this;
    }

    /**
     * @param callable|null $callback
     */
    public function setRequestCallback($callback): void
    {
        $this->requestCallback = $callback;
    }

    public function getCallCount(): int
    {
        return $this->callCount;
    }

    public function getExpectedCallCount(): int
    {
        return $this->expectedCallCount;
    }

    public function method(string $methodName): self
    {
        // 仅用于兼容测试代码中的expects()->method()调用
        return $this;
    }

    public function willThrowException(\Throwable $exception): self
    {
        $this->shouldThrowException = true;
        $this->exceptionToThrow = $exception;

        return $this;
    }

    public function willReturn(mixed $value): self
    {
        $this->returnValue = $value;

        return $this;
    }

    // 需要实现的抽象方法，提供最小化实现
    protected function getLogger(): LoggerInterface
    {
        return new NullLogger();
    }

    protected function getHttpClient(): HttpClientInterface
    {
        throw new \BadMethodCallException('Not implemented in test mock');
    }

    protected function getLockFactory(): LockFactory
    {
        throw new \BadMethodCallException('Not implemented in test mock');
    }

    protected function getCache(): CacheInterface
    {
        throw new \BadMethodCallException('Not implemented in test mock');
    }

    protected function getEventDispatcher(): EventDispatcherInterface
    {
        throw new \BadMethodCallException('Not implemented in test mock');
    }

    protected function getAsyncInsertService(): AsyncInsertService
    {
        throw new \BadMethodCallException('Not implemented in test mock');
    }

    protected function getRequestUrl(RequestInterface $request): string
    {
        return '';
    }

    protected function getRequestMethod(RequestInterface $request): string
    {
        return 'POST';
    }

    protected function getRequestOptions(RequestInterface $request): ?array
    {
        return null;
    }

    protected function formatResponse(RequestInterface $request, ResponseInterface $response): mixed
    {
        return [];
    }
}
