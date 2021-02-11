<?php

namespace Utility\Model;

class CMODEL_SMARTY extends BASE_CMODEL_SMARTY {

  public function __construct() {
    parent::__construct(MODEL_PATH::get_smarty_compile_directory(), MODEL_PATH::get_smarty_cache_directory());

    $this->disableSecurity();

    $this->registerPlugin("modifier", "pluralize", array($this, "smarty_modifier_pluralize"));
  }

  function smarty_modifier_pluralize($string) {
    return LANG_UTIL::get_plural($string);
  }
}
