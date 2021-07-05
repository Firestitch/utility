<?php

use Framework\Core\WebApplication;

$hasBootstrap = @(include_once dirname(__FILE__) . "/../../framework/Boot/webbootstrap.inc");

if (!$hasBootstrap) {
  header("location: /unavailable/");
}

WebApplication::instance()
  ->start();
