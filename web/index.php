<?php

use Framework\Core\WebApplication;
use Utility\Manager\RouteManager;

$has_bootstrap = @include_once(realpath(dirname(__FILE__) . "/../..") . "/framework/boot/webbootstrap.inc");

if (!$has_bootstrap)
  header("location: /unavailable/");

WebApplication::instance()
  ->register_manager(RouteManager::class)
  ->run();
