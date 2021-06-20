<?php

namespace Utility\Model;

use Framework\Model\SmartyModel;
use Framework\Util\FileUtil;
use Framework\Util\StringUtil;


class GeneratorModel {

  protected $_instanceDir = "";
  protected $_smartyModel = "";

  public function __construct($instanceDir) {
    $this->_instanceDir = $instanceDir;
    $this->_smartyModel = new SmartyModel();
    $this->_smartyModel->allowPhpTag();
    $this->_smartyModel->disableSecurity();

    $this->_smartyModel->registerModifierPlugin("pascalize", [StringUtil::class, "pascalize"]);
    $this->_smartyModel->registerModifierPlugin("camelize", [StringUtil::class, "camelize"]);
  }

  public static function getModelClass($basename) {
    return "Backend\\Model\\" . self::getModelClassname($basename);
  }

  public static function getModelClassname($basename) {
    return StringUtil::pascalize($basename) . "Model";
  }

  public static function getHandlerClass($basename) {
    return "Handler\\Model\\" . self::getHandlerClassname($basename);
  }

  public static function getHandlerClassname($basename) {
    return StringUtil::pascalize($basename) . "Handler";
  }

  public function getInstanceDir() {
    return $this->_instanceDir;
  }

  public function writeTemplate($template, $file) {
    $content = $this->_smartyModel->fetch($template);
    $this->write($file, $content);

    return $this;
  }

  public function write($file, $string) {
    FileUtil::put($file, $string);

    return $this;
  }

  public function assign($name, $value) {
    $this->_smartyModel->assign($name, $value);

    return $this->_smartyModel;
  }

  public function registerAutoload() {
    spl_autoload_register([$this, "autoload"], true, true);
  }

  public function createModel($model) {
    $cmodelClass = "CMODEL_" . strtoupper($model);

    return $cmodelClass::create();
  }

}
