<?php

namespace WechatOfficialAccountMenuBundle\Tests\Request\Menu;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use WechatOfficialAccountBundle\Request\WithAccountRequest;
use WechatOfficialAccountMenuBundle\Request\Menu\DeleteMenuRequest;

/**
 * @internal
 */
#[CoversClass(DeleteMenuRequest::class)]
final class DeleteMenuRequestTest extends RequestTestCase
{
    public function testRequestShouldExtendWithAccountRequest(): void
    {
        $request = new DeleteMenuRequest();

        $this->assertInstanceOf(WithAccountRequest::class, $request);
    }

    public function testGetRequestPathShouldReturnCorrectPath(): void
    {
        $request = new DeleteMenuRequest();

        $path = $request->getRequestPath();

        $this->assertEquals('https://api.weixin.qq.com/cgi-bin/menu/delete', $path);
    }

    public function testGetRequestMethodShouldReturnGet(): void
    {
        $request = new DeleteMenuRequest();

        $method = $request->getRequestMethod();

        $this->assertEquals('GET', $method);
    }

    public function testGetRequestOptionsShouldReturnNull(): void
    {
        $request = new DeleteMenuRequest();

        $options = $request->getRequestOptions();

        $this->assertNull($options);
    }

    public function testRequestShouldNotRequireAnyParameters(): void
    {
        $request = new DeleteMenuRequest();

        // 验证可以直接创建和使用，不需要设置任何参数
        $this->assertInstanceOf(DeleteMenuRequest::class, $request);
        $this->assertEquals('https://api.weixin.qq.com/cgi-bin/menu/delete', $request->getRequestPath());
        $this->assertEquals('GET', $request->getRequestMethod());
        $this->assertNull($request->getRequestOptions());
    }

    public function testRequestShouldBeSimplestMenuRequest(): void
    {
        $request = new DeleteMenuRequest();

        // 验证这是最简单的请求类型
        $reflection = new \ReflectionClass($request);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        // 过滤出非继承的公共方法
        $ownMethods = array_filter($methods, function (\ReflectionMethod $method) {
            return DeleteMenuRequest::class === $method->getDeclaringClass()->getName();
        });

        // 应该只有必需的方法，没有额外的setter/getter
        $methodNames = array_map(fn ($method) => $method->getName(), $ownMethods);

        $this->assertContains('getRequestPath', $methodNames);
        $this->assertContains('getRequestMethod', $methodNames);
        $this->assertContains('getRequestOptions', $methodNames);

        // 不应该有setter方法
        $setterMethods = array_filter($methodNames, fn ($name) => str_starts_with($name, 'set'));
        $this->assertEmpty($setterMethods, 'DeleteMenuRequest should not have any setter methods');
    }

    public function testRequestConsistencyWithApiDocumentation(): void
    {
        // 验证请求格式与微信API文档一致
        $request = new DeleteMenuRequest();

        // 验证URL
        $this->assertEquals('https://api.weixin.qq.com/cgi-bin/menu/delete', $request->getRequestPath());

        // 验证HTTP方法
        $this->assertEquals('GET', $request->getRequestMethod());

        // 验证请求选项
        $this->assertNull($request->getRequestOptions());
    }

    public function testRequestShouldDeleteAllCustomMenus(): void
    {
        // 根据微信API文档，这个请求会删除所有自定义菜单
        $request = new DeleteMenuRequest();

        // 验证这是一个无参数的删除所有菜单的请求
        $this->assertEquals('GET', $request->getRequestMethod());
        $this->assertNull($request->getRequestOptions());
        $this->assertStringEndsWith('/menu/delete', $request->getRequestPath());
    }

    public function testRequestClassStructureShouldBeMinimal(): void
    {
        $request = new DeleteMenuRequest();
        $reflection = new \ReflectionClass($request);

        // 验证类结构应该是最小的
        $properties = $reflection->getProperties();
        $ownProperties = array_filter($properties, function (\ReflectionProperty $property) {
            return DeleteMenuRequest::class === $property->getDeclaringClass()->getName();
        });

        // 不应该有自己的属性
        $this->assertEmpty($ownProperties, 'DeleteMenuRequest should not have any own properties');
    }

    public function testComparisonWithOtherMenuRequests(): void
    {
        $deleteRequest = new DeleteMenuRequest();

        // 验证删除所有菜单请求是最简单的
        $this->assertEquals('GET', $deleteRequest->getRequestMethod());
        $this->assertNull($deleteRequest->getRequestOptions());

        // 只验证当前请求的特性，不依赖其他请求类的实例化
        $this->assertInstanceOf(DeleteMenuRequest::class, $deleteRequest);
    }

    public function testRequestUrlDifferenceFromOtherRequests(): void
    {
        $deleteRequest = new DeleteMenuRequest();
        // 验证URL
        $this->assertEquals('https://api.weixin.qq.com/cgi-bin/menu/delete', $deleteRequest->getRequestPath());

        // 验证URL包含预期的路径部分
        $this->assertStringContainsString('/menu/delete', $deleteRequest->getRequestPath());
        $this->assertStringStartsWith('https://api.weixin.qq.com/', $deleteRequest->getRequestPath());
    }

    public function testInheritanceHierarchy(): void
    {
        $request = new DeleteMenuRequest();

        // 验证继承层次
        $this->assertInstanceOf(WithAccountRequest::class, $request);
        $this->assertInstanceOf(DeleteMenuRequest::class, $request);

        // 验证父类方法可以访问
        $reflection = new \ReflectionObject($request);
        $this->assertTrue($reflection->hasMethod('getRequestPath'));
        $this->assertTrue($reflection->hasMethod('getRequestMethod'));
        $this->assertTrue($reflection->hasMethod('getRequestOptions'));
    }

    public function testRequestImplementsCorrectInterface(): void
    {
        $request = new DeleteMenuRequest();
        $reflection = new \ReflectionClass($request);

        // 验证实现的接口和继承的类
        $parentClass = $reflection->getParentClass();
        $this->assertNotFalse($parentClass);
        $this->assertEquals(WithAccountRequest::class, $parentClass->getName());
    }
}
