<?php

namespace Utility\Model;

class PathModel extends \Framework\Model\PathModel {
  static function get_browscap_directory() {
    return self::get_data_directory() . "browscap/";
  }

  static function get_assets_directory() {
    return self::get_application_directory() . "assets/";
  }
  static function get_packages_directory() {
    return self::get_application_directory() . "packages/";
  }
  static function get_smarty_compile_directory() {
    return self::get_data_directory() . "smarty/compile/";
  }
  static function get_smarty_cache_directory() {
    return self::get_data_directory() . "smarty/cache/";
  }
}
