<?php

namespace Utility\Model;

class PathModel extends \Framework\Model\PathModel {
  public static function getBrowscapDirectory() {
    return self::getDataDirectory() . "browscap/";
  }
  public static function getAssetsDirectory() {
    return self::getApplicationDirectory() . "assets/";
  }
  public static function getPackagesDirectory() {
    return self::getApplicationDirectory() . "packages/";
  }
  public static function getSmartyCompileDirectory() {
    return self::getDataDirectory() . "smarty/compile/";
  }
  public static function getSmartyCacheDirectory() {
    return self::getDataDirectory() . "smarty/cache/";
  }
}
