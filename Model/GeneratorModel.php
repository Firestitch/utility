<?php

namespace Utility\Model;

use Framework\Model\SmartyModel;
use Framework\Util\FILE_UTIL;
use Framework\Util\STRING_UTIL;

class GeneratorModel {

  protected $_instance_dir   = "";
  protected $_smarty_cmodel   = "";

  function __construct($instance_dir) {
    $this->_instance_dir = $instance_dir;

    $this->_smarty_cmodel = new SmartyModel();
    $this->_smarty_cmodel->allow_php_tag();
    $this->_smarty_cmodel->disableSecurity();
  }

  function get_instance_dir() {
    return $this->_instance_dir;
  }

  function write_template($template, $file) {
    $content = $this->_smarty_cmodel->fetch($template);
    $this->write($file, $content);
    return $this;
  }

  function write($file, $string) {
    FILE_UTIL::put($file, $string);
    return $this;
  }

  function assign($name, $value) {
    $this->_smarty_cmodel->assign($name, $value);
    return $this->_smarty_cmodel;
  }

  function register_autoload() {
    spl_autoload_register(array($this, "autoload"), true, true);
  }

  function create_cmodel($model) {
    $cmodel_class = "CMODEL_" . strtoupper($model);
    return $cmodel_class::create();
  }

  static function get_model_class($basename) {
    return "Backend\Model\\" . self::get_model_classname($basename);
  }

  static function get_model_classname($basename) {
    return STRING_UTIL::pascalize($basename) . "Model";
  }

  static function get_handler_class($basename) {
    return "Handler\Model\\" . self::get_handler_classname($basename);
  }

  static function get_handler_classname($basename) {
    return STRING_UTIL::pascalize($basename) . "Handler";
  }
}
