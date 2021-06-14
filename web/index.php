<?php

use Framework\Core\WebApplication;
use Utility\Manager\RouteManager;
$hasBootstrap = @(include_once dirname(__FILE__) . "/../../framework/Boot/webbootstrap.inc");
if (!$hasBootstrap) {
    header("location: /unavailable/");
}
WebApplication::instance()->registerManager(routeManager::class)->start();