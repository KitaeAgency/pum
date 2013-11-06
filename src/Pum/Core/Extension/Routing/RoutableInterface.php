<?php

namespace Pum\Core\Extension\Routing;

interface RoutableInterface
{
    public function getSeoKey();
    public function getSeoTemplate();
    public function getSeoOrder();
}
