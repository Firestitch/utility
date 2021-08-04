<?php

use Framework\Core\WebApplication;

$hasBootstrap = @(include_once dirname(__FILE__) . "/../../framework/Boot/web.php");

if (!$hasBootstrap) {
  header("location: /unavailable/");
}

WebApplication::instance()
  ->start();
