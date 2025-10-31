<?php

declare(strict_types=1);

namespace WechatOfficialAccountMenuBundle\Test\Service;

use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;

/**
 * 测试环境专用的AdminUrlGenerator装饰器
 * 自动设置此bundle的Dashboard，解决多Dashboard冲突问题
 */
final class TestAdminUrlGenerator implements AdminUrlGeneratorInterface
{
    public function __construct(
        private readonly AdminUrlGeneratorInterface $inner,
        private readonly string $dashboardFqcn,
    ) {
    }

    /**
     * @phpstan-ignore-next-line symplify.noReturnSetterMethod (装饰器模式需要返回值以支持链式调用)
     */
    public function setAll(array $params): AdminUrlGeneratorInterface
    {
        $this->inner->setAll($params);

        return $this;
    }

    /**
     * @phpstan-ignore-next-line symplify.noReturnSetterMethod (装饰器模式需要返回值以支持链式调用)
     */
    public function set(string $name, mixed $value): AdminUrlGeneratorInterface
    {
        $this->inner->set($name, $value);

        return $this;
    }

    /**
     * @phpstan-ignore-next-line symplify.noReturnSetterMethod (装饰器模式需要返回值以支持链式调用)
     */
    public function setController(string $controllerFqcn): AdminUrlGeneratorInterface
    {
        $this->inner->setController($controllerFqcn);

        return $this;
    }

    /**
     * @phpstan-ignore-next-line symplify.noReturnSetterMethod (装饰器模式需要返回值以支持链式调用)
     */
    public function setAction(string $action): AdminUrlGeneratorInterface
    {
        $this->inner->setAction($action);

        return $this;
    }

    /**
     * @phpstan-ignore-next-line symplify.noReturnSetterMethod (装饰器模式需要返回值以支持链式调用)
     */
    public function setEntityId(mixed $entityId): AdminUrlGeneratorInterface
    {
        $this->inner->setEntityId($entityId);

        return $this;
    }

    /**
     * @phpstan-ignore-next-line symplify.noReturnSetterMethod (装饰器模式需要返回值以支持链式调用)
     */
    public function setDashboard(string $dashboardControllerFqcn): AdminUrlGeneratorInterface
    {
        $this->inner->setDashboard($dashboardControllerFqcn);

        return $this;
    }

    /**
     * @phpstan-ignore-next-line symplify.noReturnSetterMethod (装饰器模式需要返回值以支持链式调用)
     */
    public function setRoute(string $routeName, array $routeParameters = []): AdminUrlGeneratorInterface
    {
        $this->inner->setRoute($routeName, $routeParameters);

        return $this;
    }

    public function unsetAll(): AdminUrlGeneratorInterface
    {
        $this->inner->unsetAll();

        return $this;
    }

    public function unset(string $name): AdminUrlGeneratorInterface
    {
        $this->inner->unset($name);

        return $this;
    }

    public function unsetAllExcept(string ...$namesOfParamsToKeep): AdminUrlGeneratorInterface
    {
        $this->inner->unsetAllExcept(...$namesOfParamsToKeep);

        return $this;
    }

    public function get(string $name): mixed
    {
        return $this->inner->get($name);
    }

    public function includeReferrer(): AdminUrlGeneratorInterface
    {
        $this->inner->includeReferrer();

        return $this;
    }

    public function removeReferrer(): AdminUrlGeneratorInterface
    {
        $this->inner->removeReferrer();

        return $this;
    }

    /**
     * @phpstan-ignore-next-line symplify.noReturnSetterMethod (装饰器模式需要返回值以支持链式调用)
     */
    public function setReferrer(string $referrer): AdminUrlGeneratorInterface
    {
        $this->inner->setReferrer($referrer);

        return $this;
    }

    public function addSignature(bool $addSignature = true): AdminUrlGeneratorInterface
    {
        $this->inner->addSignature($addSignature);

        return $this;
    }

    public function getSignature(): string
    {
        return $this->inner->getSignature();
    }

    public function generateUrl(): string
    {
        // 在生成URL之前，如果没有设置Dashboard，自动设置此bundle的Dashboard
        if (null === $this->get('dashboardControllerFqcn')) {
            $this->setDashboard($this->dashboardFqcn);
        }

        return $this->inner->generateUrl();
    }
}
