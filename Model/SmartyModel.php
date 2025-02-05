<?php

namespace Utility\Model;

use Smarty\Smarty;


class SmartyModel extends \Framework\Model\SmartyModel {

  public function __construct() {
    parent::__construct();

    $phpModifiers = [
      "isset",
      "empty",
      "count",
      "sizeof",
      "in_array",
      "is_array",
      "time",
      "escape",
      "nl2br",
      "trim",
      "array_splice",
      "array_reverse",
      "array_keys",
      "array_values",
      "array_rand",
      "array_sum",
      "preg_match",
      "sort",
      "strtoupper",
      "strtolower",
      "implode",
      "explode",
      "substr",
      "strlen",
      "number_format",
      "round",
      "array_key_exists",
    ];

    foreach ($phpModifiers as $phpModifier) {
      $this->registerPlugin(Smarty::PLUGIN_MODIFIER, $phpModifier, $phpModifier);
    }
  }

}