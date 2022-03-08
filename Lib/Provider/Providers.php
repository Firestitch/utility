<?php

namespace Utility\Lib\Provider;

use Utility\Lib\Provider\RouteManager;
use Utility\Lib\Provider\SessionManager;
use Utility\Lib\Provider\SystemManager;


class Providers {
  public static function get() {
    return [
      SystemManager::class,
      SessionManager::class,
      RouteManager::class
    ];
  }
}
