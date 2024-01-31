<?php

namespace Utility\View\Namespaces;

use Framework\Arry\Arry;
use Framework\Core\View;
use Framework\Core\WebApplication;
use Framework\Util\FileUtil;
use Framework\Util\StringUtil;


class NamespacesView extends View {

  private $_label = "Namespace";

  private $_name = "namespace";

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
      ->setVar("class", str_replace("_", "-", StringUtil::snakeize($this->_name)))
      ->setVar("name", $this->_name)
      ->setVar("label", $this->_label);
  }

  public function setName($name) {
    $this->_name = $name;
    return $this;
  }

  public function setLabel($label) {
    $this->_label = $label;
    return $this;
  }
}
