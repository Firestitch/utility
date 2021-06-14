<?php

namespace Utility\Model;

use Framework\Model\SmartyModel;
use Framework\Util\FileUtil;
use Framework\Util\StringUtil;

class GeneratorModel {
  protected $_instanceDir = "";
  protected $_smartyModel = "";
  function __construct($instanceDir) {
    $this->_instanceDir = $instanceDir;
    $this->_smartyModel = new SmartyModel();
    $this->_smartyModel->allowPhpTag();
    $this->_smartyModel->disableSecurity();

    $this->_smartyModel->registerModifierPlugin("pascalize", [StringUtil::class, "pascalize"]);
    $this->_smartyModel->registerModifierPlugin("camelizeize", [StringUtil::class, "camelize"]);
  }
  function getInstanceDir() {
    return $this->_instanceDir;
  }
  function writeTemplate($template, $file) {
    $content = $this->_smartyModel->fetch($template);
    $this->write($file, $content);
    return $this;
  }
  function write($file, $string) {
    FileUtil::put($file, $string);
    return $this;
  }
  function assign($name, $value) {
    $this->_smartyModel->assign($name, $value);
    return $this->_smartyModel;
  }
  function registerAutoload() {
    spl_autoload_register(array($this, "autoload"), true, true);
  }
  function createModel($model) {
    $cmodelClass = "CMODEL_" . strtoupper($model);
    return $cmodelClass::create();
  }
  static function getModelClass($basename) {
    return "Backend\\Model\\" . self::getModelClassname($basename);
  }
  static function getModelClassname($basename) {
    return StringUtil::pascalize($basename) . "Model";
  }
  static function getHandlerClass($basename) {
    return "Handler\\Model\\" . self::getHandlerClassname($basename);
  }
  static function getHandlerClassname($basename) {
    return StringUtil::pascalize($basename) . "Handler";
  }
}
