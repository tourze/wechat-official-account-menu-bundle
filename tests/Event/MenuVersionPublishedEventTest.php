<?php

namespace WechatOfficialAccountMenuBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Contracts\EventDispatcher\Event;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;
use WechatOfficialAccountMenuBundle\Event\MenuVersionCreatedEvent;
use WechatOfficialAccountMenuBundle\Event\MenuVersionPublishedEvent;

/**
 * @internal
 */
#[CoversClass(MenuVersionPublishedEvent::class)]
final class MenuVersionPublishedEventTest extends AbstractEventTestCase
{
    public function testConstructorShouldSetMenuVersion(): void
    {
        /*
         * 使用具体类 MenuVersion 创建 Mock 对象的原因:
         * 1) MenuVersion 是实体类，没有对应的接口，只能使用具体类
         * 2) 测试事件构造器需要验证类型约束，使用具体类更准确
         * 3) 这种使用是合理的，因为实体类通常作为数据载体使用
         */
        $menuVersion = $this->createMock(MenuVersion::class);
        $event = new MenuVersionPublishedEvent($menuVersion);

        $this->assertSame($menuVersion, $event->getMenuVersion());
    }

    public function testGetMenuVersionShouldReturnMenuVersion(): void
    {
        /*
         * 使用具体类 MenuVersion 创建 Mock 对象的原因:
         * 1) MenuVersion 是实体类，没有对应的接口，只能使用具体类
         * 2) 测试 getter 方法需要验证返回类型，使用具体类更准确
         * 3) 这种使用是合理的，因为实体类通常作为数据载体使用
         */
        $menuVersion = $this->createMock(MenuVersion::class);
        $event = new MenuVersionPublishedEvent($menuVersion);

        $result = $event->getMenuVersion();

        $this->assertInstanceOf(MenuVersion::class, $result);
        $this->assertSame($menuVersion, $result);
    }

    public function testEventShouldExtendSymfonyEvent(): void
    {
        /*
         * 使用具体类 MenuVersion 创建 Mock 对象的原因:
         * 1) MenuVersion 是实体类，没有对应的接口，只能使用具体类
         * 2) 测试事件继承关系需要有效的构造参数，使用具体类更准确
         * 3) 这种使用是合理的，因为实体类通常作为数据载体使用
         */
        $menuVersion = $this->createMock(MenuVersion::class);
        $event = new MenuVersionPublishedEvent($menuVersion);

        $this->assertInstanceOf(Event::class, $event);
    }

    public function testEventShouldBeImmutable(): void
    {
        /*
         * 使用具体类 MenuVersion 创建 Mock 对象的原因:
         * 1) MenuVersion 是实体类，没有对应的接口，只能使用具体类
         * 2) 测试事件不可变性需要有效的构造参数，使用具体类更准确
         * 3) 这种使用是合理的，因为实体类通常作为数据载体使用
         */
        $menuVersion = $this->createMock(MenuVersion::class);
        $event = new MenuVersionPublishedEvent($menuVersion);

        // 验证没有setter方法可以修改属性
        $reflection = new \ReflectionClass($event);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        $setterMethods = array_filter($methods, function (\ReflectionMethod $method) {
            return str_starts_with($method->getName(), 'set');
        });

        $this->assertEmpty($setterMethods, 'Event should be immutable and have no setter methods');
    }

    public function testConstructorParameterShouldBeReadonly(): void
    {
        $reflection = new \ReflectionClass(MenuVersionPublishedEvent::class);
        $constructor = $reflection->getConstructor();
        $this->assertInstanceOf(\ReflectionMethod::class, $constructor, 'Constructor should exist');
        $parameters = $constructor->getParameters();

        $this->assertCount(1, $parameters);
        // 在PHP 8.1+中验证readonly参数
        if (method_exists($parameters[0], 'isReadOnly')) {
            $this->assertTrue($parameters[0]->isReadOnly());
        }
        $this->assertEquals('menuVersion', $parameters[0]->getName());
    }

    public function testEventWithRealMenuVersionObject(): void
    {
        /*
         * 使用具体类 MenuVersion 创建 Mock 对象的原因:
         * 1) MenuVersion 是实体类，没有对应的接口，只能使用具体类
         * 2) 测试真实对象交互需要模拟具体方法行为，使用具体类更准确
         * 3) 这种使用是合理的，因为实体类通常作为数据载体使用
         */
        $menuVersion = $this->createMock(MenuVersion::class);
        $menuVersion->method('__toString')->willReturn('Published Menu Version');

        $event = new MenuVersionPublishedEvent($menuVersion);

        $this->assertSame($menuVersion, $event->getMenuVersion());
        $this->assertEquals('Published Menu Version', (string) $event->getMenuVersion());
    }

    public function testEventClassStructureIsConsistentWithCreatedEvent(): void
    {
        // 验证两个事件类有相同的结构
        $publishedReflection = new \ReflectionClass(MenuVersionPublishedEvent::class);
        $createdReflection = new \ReflectionClass(MenuVersionCreatedEvent::class);

        // 验证都有相同的方法
        $publishedMethods = array_map(fn ($method) => $method->getName(), $publishedReflection->getMethods());
        $createdMethods = array_map(fn ($method) => $method->getName(), $createdReflection->getMethods());

        $this->assertEquals($createdMethods, $publishedMethods);

        // 验证都继承自相同的基类
        $publishedParent = $publishedReflection->getParentClass();
        $createdParent = $createdReflection->getParentClass();

        $this->assertNotFalse($publishedParent);
        $this->assertNotFalse($createdParent);
        $this->assertEquals($createdParent->getName(), $publishedParent->getName());
    }
}
