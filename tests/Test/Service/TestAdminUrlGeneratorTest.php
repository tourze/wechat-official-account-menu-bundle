<?php

declare(strict_types=1);

namespace WechatOfficialAccountMenuBundle\Tests\Test\Service;

use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WechatOfficialAccountMenuBundle\Test\Service\TestAdminUrlGenerator;

/**
 * @internal
 */
#[CoversClass(TestAdminUrlGenerator::class)]
final class TestAdminUrlGeneratorTest extends TestCase
{
    public function testGenerateUrlAutoSetsDashboardWhenMissing(): void
    {
        $inner = new class implements AdminUrlGeneratorInterface {
            /** @var array<string, mixed> */
            private array $params = [];

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setDashboard(string $dashboardControllerFqcn): self
            {
                $this->params['dashboardControllerFqcn'] = $dashboardControllerFqcn;

                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setController(string $crudControllerFqcn): self
            {
                $this->params['crudControllerFqcn'] = $crudControllerFqcn;

                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setAction(string $action): self
            {
                $this->params['action'] = $action;

                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setRoute(string $routeName, array $routeParameters = []): self
            {
                $this->params['routeName'] = $routeName;
                $this->params['routeParameters'] = $routeParameters;

                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setEntityId(mixed $entityId): self
            {
                $this->params['entityId'] = $entityId;

                return $this;
            }

            public function get(string $paramName): mixed
            {
                return $this->params[$paramName] ?? null;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function set(string $paramName, mixed $paramValue): self
            {
                $this->params[$paramName] = $paramValue;

                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setAll(array $routeParameters): self
            {
                foreach ($routeParameters as $k => $v) {
                    $this->params[$k] = $v;
                }

                return $this;
            }

              public function unset(string $paramName): self
            {
                unset($this->params[$paramName]);

                return $this;
            }

              public function unsetAll(): self
            {
                $this->params = [];

                return $this;
            }

              public function unsetAllExcept(string ...$namesOfParamsToKeep): self
            {
                $keep = [];
                foreach ($namesOfParamsToKeep as $name) {
                    if (array_key_exists($name, $this->params)) {
                        $keep[$name] = $this->params[$name];
                    }
                }
                $this->params = $keep;

                return $this;
            }

              public function includeReferrer(): self
            {
                return $this;
            }

              public function removeReferrer(): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setReferrer(string $referrer): self
            {
                return $this;
            }

              public function addSignature(bool $addSignature = true): self
            {
                return $this;
            }

            public function getSignature(): string
            {
                return 'sig';
            }

            public function generateUrl(): string
            {
                return 'ok';
            }

            /** @return array<string, mixed> */
            public function debugParams(): array
            {
                return $this->params;
            }
        };

        $decorator = new TestAdminUrlGenerator($inner, 'App\DummyDashboard');
        $url = $decorator->generateUrl();

        $this->assertSame('ok', $url);
        // 断言已自动设置 Dashboard
        $this->assertSame('App\DummyDashboard', $inner->get('dashboardControllerFqcn'));
    }

    public function testChainedSettersPassThrough(): void
    {
        $inner = new class implements AdminUrlGeneratorInterface {
            public string $lastAction = '';

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setDashboard(string $dashboardControllerFqcn): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setController(string $crudControllerFqcn): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setAction(string $action): self
            {
                $this->lastAction = $action;

                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setRoute(string $routeName, array $routeParameters = []): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setEntityId(mixed $entityId): self
            {
                return $this;
            }

            public function get(string $paramName): mixed
            {
                return null;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function set(string $paramName, mixed $paramValue): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setAll(array $routeParameters): self
            {
                return $this;
            }

              public function unset(string $paramName): self
            {
                return $this;
            }

              public function unsetAll(): self
            {
                return $this;
            }

              public function unsetAllExcept(string ...$namesOfParamsToKeep): self
            {
                return $this;
            }

              public function includeReferrer(): self
            {
                return $this;
            }

              public function removeReferrer(): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setReferrer(string $referrer): self
            {
                return $this;
            }

              public function addSignature(bool $addSignature = true): self
            {
                return $this;
            }

            public function getSignature(): string
            {
                return '';
            }

            public function generateUrl(): string
            {
                return 'url';
            }
        };

        $decorator = new TestAdminUrlGenerator($inner, 'App\DummyDashboard');
        $result = $decorator->setAction('INDEX')->setAll(['foo' => 'bar']);
        $this->assertSame($decorator, $result);
    }

    public function testGetAndSetWork(): void
    {
        $inner = new class implements AdminUrlGeneratorInterface {
            /** @var array<string, mixed> */
            private array $p = [];

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setDashboard(string $dashboardControllerFqcn): self
            {
                $this->p['dashboardControllerFqcn'] = $dashboardControllerFqcn;

                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setController(string $crudControllerFqcn): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setAction(string $action): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setRoute(string $routeName, array $routeParameters = []): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setEntityId(mixed $entityId): self
            {
                return $this;
            }

            public function get(string $paramName): mixed
            {
                return $this->p[$paramName] ?? null;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function set(string $paramName, mixed $paramValue): self
            {
                $this->p[$paramName] = $paramValue;

                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setAll(array $routeParameters): self
            {
                foreach ($routeParameters as $k => $v) {
                    $this->p[$k] = $v;
                }

                return $this;
            }

              public function unset(string $paramName): self
            {
                unset($this->p[$paramName]);

                return $this;
            }

              public function unsetAll(): self
            {
                $this->p = [];

                return $this;
            }

              public function unsetAllExcept(string ...$namesOfParamsToKeep): self
            {
                $keep = [];
                foreach ($namesOfParamsToKeep as $n) {
                    if (isset($this->p[$n])) {
                        $keep[$n] = $this->p[$n];
                    }
                }$this->p = $keep;

                return $this;
            }

              public function includeReferrer(): self
            {
                return $this;
            }

              public function removeReferrer(): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setReferrer(string $referrer): self
            {
                return $this;
            }

              public function addSignature(bool $addSignature = true): self
            {
                return $this;
            }

            public function getSignature(): string
            {
                return 's';
            }

            public function generateUrl(): string
            {
                return 'u';
            }
        };

        $decorator = new TestAdminUrlGenerator($inner, 'App\DummyDashboard');
        $decorator->set('foo', 'bar');
        $this->assertSame('bar', $decorator->get('foo'));
        $decorator->unset('foo');
        $this->assertNull($decorator->get('foo'));
        $decorator->setAll(['a' => 1, 'b' => 2]);
        $decorator->unsetAllExcept('a');
        $this->assertSame(1, $decorator->get('a'));
        $decorator->unsetAll();
        $this->assertNull($decorator->get('a'));
    }

    public function testSet(): void
    {
        $inner = new class implements AdminUrlGeneratorInterface {
            /** @var array<string, mixed> */
            private array $p = [];

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setDashboard(string $dashboardControllerFqcn): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setController(string $crudControllerFqcn): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setAction(string $action): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setRoute(string $routeName, array $routeParameters = []): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setEntityId(mixed $entityId): self
            {
                return $this;
            }

            public function get(string $paramName): mixed
            {
                return $this->p[$paramName] ?? null;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function set(string $paramName, mixed $paramValue): self
            {
                $this->p[$paramName] = $paramValue;

                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setAll(array $routeParameters): self
            {
                return $this;
            }

              public function unset(string $paramName): self
            {
                return $this;
            }

              public function unsetAll(): self
            {
                return $this;
            }

              public function unsetAllExcept(string ...$namesOfParamsToKeep): self
            {
                return $this;
            }

              public function includeReferrer(): self
            {
                return $this;
            }

              public function removeReferrer(): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setReferrer(string $referrer): self
            {
                return $this;
            }

              public function addSignature(bool $addSignature = true): self
            {
                return $this;
            }

            public function getSignature(): string
            {
                return '';
            }

            public function generateUrl(): string
            {
                return '';
            }
        };
        $decorator = new TestAdminUrlGenerator($inner, 'App\Dummy');
        $decorator->set('k', 'v');
        $this->assertSame('v', $decorator->get('k'));
    }

    public function testUnset(): void
    {
        $inner = new class implements AdminUrlGeneratorInterface {
            /** @var array<string, mixed> */
            private array $p = [];

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setDashboard(string $dashboardControllerFqcn): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setController(string $crudControllerFqcn): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setAction(string $action): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setRoute(string $routeName, array $routeParameters = []): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setEntityId(mixed $entityId): self
            {
                return $this;
            }

            public function get(string $paramName): mixed
            {
                return $this->p[$paramName] ?? null;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function set(string $paramName, mixed $paramValue): self
            {
                $this->p[$paramName] = $paramValue;

                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setAll(array $routeParameters): self
            {
                return $this;
            }

              public function unset(string $paramName): self
            {
                unset($this->p[$paramName]);

                return $this;
            }

              public function unsetAll(): self
            {
                return $this;
            }

              public function unsetAllExcept(string ...$namesOfParamsToKeep): self
            {
                return $this;
            }

              public function includeReferrer(): self
            {
                return $this;
            }

              public function removeReferrer(): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setReferrer(string $referrer): self
            {
                return $this;
            }

              public function addSignature(bool $addSignature = true): self
            {
                return $this;
            }

            public function getSignature(): string
            {
                return '';
            }

            public function generateUrl(): string
            {
                return '';
            }
        };
        $decorator = new TestAdminUrlGenerator($inner, 'App\Dummy');
        $decorator->set('k', 'v');
        $decorator->unset('k');
        $this->assertNull($decorator->get('k'));
    }

    public function testUnsetAll(): void
    {
        $inner = new class implements AdminUrlGeneratorInterface {
            /** @var array<string, mixed> */
            private array $p = [];

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setDashboard(string $dashboardControllerFqcn): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setController(string $crudControllerFqcn): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setAction(string $action): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setRoute(string $routeName, array $routeParameters = []): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setEntityId(mixed $entityId): self
            {
                return $this;
            }

            public function get(string $paramName): mixed
            {
                return $this->p[$paramName] ?? null;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function set(string $paramName, mixed $paramValue): self
            {
                $this->p[$paramName] = $paramValue;

                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setAll(array $routeParameters): self
            {
                return $this;
            }

              public function unset(string $paramName): self
            {
                return $this;
            }

              public function unsetAll(): self
            {
                $this->p = [];

                return $this;
            }

              public function unsetAllExcept(string ...$namesOfParamsToKeep): self
            {
                return $this;
            }

              public function includeReferrer(): self
            {
                return $this;
            }

              public function removeReferrer(): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setReferrer(string $referrer): self
            {
                return $this;
            }

              public function addSignature(bool $addSignature = true): self
            {
                return $this;
            }

            public function getSignature(): string
            {
                return '';
            }

            public function generateUrl(): string
            {
                return '';
            }
        };
        $decorator = new TestAdminUrlGenerator($inner, 'App\Dummy');
        $decorator->set('k', 'v');
        $decorator->unsetAll();
        $this->assertNull($decorator->get('k'));
    }

    public function testUnsetAllExcept(): void
    {
        $inner = new class implements AdminUrlGeneratorInterface {
            /** @var array<string, mixed> */
            private array $p = [];

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setDashboard(string $dashboardControllerFqcn): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setController(string $crudControllerFqcn): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setAction(string $action): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setRoute(string $routeName, array $routeParameters = []): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setEntityId(mixed $entityId): self
            {
                return $this;
            }

            public function get(string $paramName): mixed
            {
                return $this->p[$paramName] ?? null;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function set(string $paramName, mixed $paramValue): self
            {
                $this->p[$paramName] = $paramValue;

                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setAll(array $routeParameters): self
            {
                $this->p = $routeParameters;

                return $this;
            }

              public function unset(string $paramName): self
            {
                return $this;
            }

              public function unsetAll(): self
            {
                return $this;
            }

              public function unsetAllExcept(string ...$namesOfParamsToKeep): self
            {
                $keep = [];
                foreach ($namesOfParamsToKeep as $n) {
                    if (array_key_exists($n, $this->p)) {
                        $keep[$n] = $this->p[$n];
                    }
                }$this->p = $keep;

                return $this;
            }

              public function includeReferrer(): self
            {
                return $this;
            }

              public function removeReferrer(): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setReferrer(string $referrer): self
            {
                return $this;
            }

              public function addSignature(bool $addSignature = true): self
            {
                return $this;
            }

            public function getSignature(): string
            {
                return '';
            }

            public function generateUrl(): string
            {
                return '';
            }
        };
        $decorator = new TestAdminUrlGenerator($inner, 'App\Dummy');
        $decorator->setAll(['k' => 'v', 'x' => 'y']);
        $decorator->unsetAllExcept('k');
        $this->assertSame('v', $decorator->get('k'));
        $this->assertNull($decorator->get('x'));
    }

    public function testIncludeReferrer(): void
    {
        $inner = new class implements AdminUrlGeneratorInterface {
              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setDashboard(string $dashboardControllerFqcn): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setController(string $crudControllerFqcn): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setAction(string $action): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setRoute(string $routeName, array $routeParameters = []): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setEntityId(mixed $entityId): self
            {
                return $this;
            }

            public function get(string $paramName): mixed
            {
                return null;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function set(string $paramName, mixed $paramValue): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setAll(array $routeParameters): self
            {
                return $this;
            }

              public function unset(string $paramName): self
            {
                return $this;
            }

              public function unsetAll(): self
            {
                return $this;
            }

              public function unsetAllExcept(string ...$namesOfParamsToKeep): self
            {
                return $this;
            }

              public function includeReferrer(): self
            {
                return $this;
            }

              public function removeReferrer(): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setReferrer(string $referrer): self
            {
                return $this;
            }

              public function addSignature(bool $addSignature = true): self
            {
                return $this;
            }

            public function getSignature(): string
            {
                return '';
            }

            public function generateUrl(): string
            {
                return '';
            }
        };
        $decorator = new TestAdminUrlGenerator($inner, 'App\Dummy');
        // 覆盖弃用方法调用
        /** @phpstan-ignore-next-line method.deprecated */
        $this->assertSame($decorator, $decorator->includeReferrer());
    }

    public function testRemoveReferrer(): void
    {
        $inner = new class implements AdminUrlGeneratorInterface {
              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setDashboard(string $dashboardControllerFqcn): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setController(string $crudControllerFqcn): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setAction(string $action): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setRoute(string $routeName, array $routeParameters = []): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setEntityId(mixed $entityId): self
            {
                return $this;
            }

            public function get(string $paramName): mixed
            {
                return null;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function set(string $paramName, mixed $paramValue): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setAll(array $routeParameters): self
            {
                return $this;
            }

              public function unset(string $paramName): self
            {
                return $this;
            }

              public function unsetAll(): self
            {
                return $this;
            }

              public function unsetAllExcept(string ...$namesOfParamsToKeep): self
            {
                return $this;
            }

              public function includeReferrer(): self
            {
                return $this;
            }

              public function removeReferrer(): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setReferrer(string $referrer): self
            {
                return $this;
            }

              public function addSignature(bool $addSignature = true): self
            {
                return $this;
            }

            public function getSignature(): string
            {
                return '';
            }

            public function generateUrl(): string
            {
                return '';
            }
        };
        $decorator = new TestAdminUrlGenerator($inner, 'App\Dummy');
        /** @phpstan-ignore-next-line method.deprecated */
        $this->assertSame($decorator, $decorator->removeReferrer());
    }

    public function testAddSignatureAndGetSignature(): void
    {
        $inner = new class implements AdminUrlGeneratorInterface {
              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setDashboard(string $dashboardControllerFqcn): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setController(string $crudControllerFqcn): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setAction(string $action): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setRoute(string $routeName, array $routeParameters = []): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setEntityId(mixed $entityId): self
            {
                return $this;
            }

            public function get(string $paramName): mixed
            {
                return null;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function set(string $paramName, mixed $paramValue): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setAll(array $routeParameters): self
            {
                return $this;
            }

              public function unset(string $paramName): self
            {
                return $this;
            }

              public function unsetAll(): self
            {
                return $this;
            }

              public function unsetAllExcept(string ...$namesOfParamsToKeep): self
            {
                return $this;
            }

              public function includeReferrer(): self
            {
                return $this;
            }

              public function removeReferrer(): self
            {
                return $this;
            }

              /** @phpstan-ignore-next-line symplify.noReturnSetterMethod */
              public function setReferrer(string $referrer): self
            {
                return $this;
            }

              public function addSignature(bool $addSignature = true): self
            {
                return $this;
            }

            public function getSignature(): string
            {
                return 'sig';
            }

            public function generateUrl(): string
            {
                return '';
            }
        };
        $decorator = new TestAdminUrlGenerator($inner, 'App\Dummy');
        /** @phpstan-ignore-next-line method.deprecated */
        $this->assertSame($decorator, $decorator->addSignature());
        /** @phpstan-ignore-next-line method.deprecated */
        $this->assertSame('sig', $decorator->getSignature());
    }
}
