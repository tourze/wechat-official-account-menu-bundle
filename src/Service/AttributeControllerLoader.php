<?php

namespace WechatOfficialAccountMenuBundle\Service;

use Symfony\Bundle\FrameworkBundle\Routing\AttributeRouteControllerLoader;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\RouteCollection;
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface;
use WechatOfficialAccountMenuBundle\Controller\Api\CreateFromCurrentController;
use WechatOfficialAccountMenuBundle\Controller\Api\CreateMenuController as ApiCreateMenuController;
use WechatOfficialAccountMenuBundle\Controller\Api\DeleteMenuController as ApiDeleteMenuController;
use WechatOfficialAccountMenuBundle\Controller\Api\GetMenusController;
use WechatOfficialAccountMenuBundle\Controller\Api\UpdateMenuController as ApiUpdateMenuController;
use WechatOfficialAccountMenuBundle\Controller\Api\UpdatePositionsController;
use WechatOfficialAccountMenuBundle\Controller\ArchiveMenuVersionController;
use WechatOfficialAccountMenuBundle\Controller\CompareMenuVersionsController;
use WechatOfficialAccountMenuBundle\Controller\CopyMenuController;
use WechatOfficialAccountMenuBundle\Controller\CreateMenuController;
use WechatOfficialAccountMenuBundle\Controller\CreateMenuVersionController;
use WechatOfficialAccountMenuBundle\Controller\DeleteMenuController;
use WechatOfficialAccountMenuBundle\Controller\GetCurrentMenuVersionController;
use WechatOfficialAccountMenuBundle\Controller\GetDraftMenuVersionController;
use WechatOfficialAccountMenuBundle\Controller\GetMenuTreeController;
use WechatOfficialAccountMenuBundle\Controller\GetMenuVersionController;
use WechatOfficialAccountMenuBundle\Controller\ListMenuVersionsController;
use WechatOfficialAccountMenuBundle\Controller\MoveMenuController;
use WechatOfficialAccountMenuBundle\Controller\PreviewMenuController;
use WechatOfficialAccountMenuBundle\Controller\PreviewMenuVersionController;
use WechatOfficialAccountMenuBundle\Controller\PublishMenuVersionController;
use WechatOfficialAccountMenuBundle\Controller\RollbackMenuVersionController;
use WechatOfficialAccountMenuBundle\Controller\SortMenuController;
use WechatOfficialAccountMenuBundle\Controller\Test\LoginController;
use WechatOfficialAccountMenuBundle\Controller\Test\LogoutController;
use WechatOfficialAccountMenuBundle\Controller\ToggleMenuController;
use WechatOfficialAccountMenuBundle\Controller\UpdateMenuController;
use WechatOfficialAccountMenuBundle\Controller\ValidateMenuController;

#[AutoconfigureTag(name: 'routing.loader')]
class AttributeControllerLoader extends Loader implements RoutingAutoLoaderInterface
{
    private AttributeRouteControllerLoader $controllerLoader;

    public function __construct()
    {
        parent::__construct();
        $this->controllerLoader = new AttributeRouteControllerLoader();
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        return $this->autoload();
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return 'attribute' === $type;
    }

    public function autoload(): RouteCollection
    {
        $collection = new RouteCollection();

        // Register API controllers
        $collection->addCollection($this->controllerLoader->load(CreateFromCurrentController::class));
        $collection->addCollection($this->controllerLoader->load(ApiCreateMenuController::class));
        $collection->addCollection($this->controllerLoader->load(ApiDeleteMenuController::class));
        $collection->addCollection($this->controllerLoader->load(GetMenusController::class));
        $collection->addCollection($this->controllerLoader->load(ApiUpdateMenuController::class));
        $collection->addCollection($this->controllerLoader->load(UpdatePositionsController::class));

        // Register Menu controllers
        $collection->addCollection($this->controllerLoader->load(CreateMenuController::class));
        $collection->addCollection($this->controllerLoader->load(UpdateMenuController::class));
        $collection->addCollection($this->controllerLoader->load(DeleteMenuController::class));
        $collection->addCollection($this->controllerLoader->load(CopyMenuController::class));
        $collection->addCollection($this->controllerLoader->load(ToggleMenuController::class));
        $collection->addCollection($this->controllerLoader->load(MoveMenuController::class));
        $collection->addCollection($this->controllerLoader->load(SortMenuController::class));
        $collection->addCollection($this->controllerLoader->load(PreviewMenuController::class));
        $collection->addCollection($this->controllerLoader->load(ValidateMenuController::class));
        $collection->addCollection($this->controllerLoader->load(GetMenuTreeController::class));

        // Register Menu Version controllers
        $collection->addCollection($this->controllerLoader->load(ListMenuVersionsController::class));
        $collection->addCollection($this->controllerLoader->load(CreateMenuVersionController::class));
        $collection->addCollection($this->controllerLoader->load(CompareMenuVersionsController::class));
        $collection->addCollection($this->controllerLoader->load(GetMenuVersionController::class));
        $collection->addCollection($this->controllerLoader->load(PublishMenuVersionController::class));
        $collection->addCollection($this->controllerLoader->load(RollbackMenuVersionController::class));
        $collection->addCollection($this->controllerLoader->load(ArchiveMenuVersionController::class));
        $collection->addCollection($this->controllerLoader->load(PreviewMenuVersionController::class));
        $collection->addCollection($this->controllerLoader->load(GetCurrentMenuVersionController::class));
        $collection->addCollection($this->controllerLoader->load(GetDraftMenuVersionController::class));

        // Register Test controllers
        $collection->addCollection($this->controllerLoader->load(LoginController::class));
        $collection->addCollection($this->controllerLoader->load(LogoutController::class));

        return $collection;
    }
}
