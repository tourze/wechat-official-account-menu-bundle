<?php

namespace WechatOfficialAccountMenuBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;

class MenuVersionCreatedEvent extends Event
{
    public function __construct(
        private readonly MenuVersion $menuVersion,
    ) {
    }

    public function getMenuVersion(): MenuVersion
    {
        return $this->menuVersion;
    }
}
