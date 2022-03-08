<?php

use Framework\Core\WebApplication;
use Utility\Lib\Provider\Providers;


$hasBootstrap = @(include_once dirname(__FILE__) . "/../../framework/Boot/web.php");

if (!$hasBootstrap) {
  header("location: /unavailable/");
}

WebApplication::instance()
  ->registerProviders(Providers::get())
  ->start();
