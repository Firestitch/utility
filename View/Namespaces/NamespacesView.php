<?php

namespace Utility\View\Namespaces;

use Framework\Arry\Arry;
use Framework\Core\View;
use Framework\Core\WebApplication;
use Framework\Util\FileUtil;


class NamespacesView extends View {

  private $_label = "Namespace";

  private $_class = "namespace";

  public function __construct() {
    $this
      ->setTemplate("./NamespacesTemplate.php")
      ->setStyle("./Namespaces.scss")
      ->disableAuthorization();
  }

  public function init() {
    $backendNamespaces = [];
    $frameworkNamespaces = [];

    $dir = WebApplication::getInstanceDirectory();
    foreach (FileUtil::getDirectoryListing($dir . "backend/Lib/", "*", false, true) as $libDir) {
      $namespace = "Backend\\Lib\\" . $libDir;
      $backendNamespaces[$namespace] = $libDir;
    }

    Arry::create(FileUtil::getDirectoryListing(WebApplication::getFrameworkDirectory(), "*", false, true))
      ->filter(function ($dir) {
        return preg_match("/^[A-Z0-9]/", $dir);
      })
      ->forEach(function ($dir) use (&$frameworkNamespaces) {
        $namespace = "Framework\\" . $dir;
        $frameworkNamespaces[$namespace] = $dir;
      });

    $namespaces = [
      "Backend" => "Backend",
      "Backend\Lib" => $backendNamespaces,
      "Framework" => $frameworkNamespaces,
    ];

    $this
      ->setVar("namespaces", $namespaces)
      ->setVar("class", $this->_class)
      ->setVar("label", $this->_label);
  }

  public function setClass($class) {
    $this->_class = $class;
    return $this;
  }

  public function setLabel($label) {
    $this->_label = $label;
    return $this;
  }
}
