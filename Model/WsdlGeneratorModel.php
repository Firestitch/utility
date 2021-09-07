<?php

namespace Utility\Model;

use Exception;
use Framework\Util\FileUtil;
use Framework\Util\HtmlUtil;
use ReflectionClass;
use ReflectionMethod;


class WsdlGeneratorModel extends GeneratorModel {

  protected $_dir = "";
  protected $_options = [];

  public function __construct($dir, $api, $options = []) {
    parent::__construct($dir);
    $this->_options = $options;
    $this->_api = $api;
  }

  public function generate($override, &$messages = []) {
    if (!is_file($this->getWsdlFile())) {
      $this->assign("api", $this->_api);
      if (!$this->writeTemplate(PathModel::getAssetsDirectory() . "wsdl.inc", $this->getWsdlFile())) {
        throw new Exception("Failed to generate " . $this->getWsdlFile());
      }
      $messages[] = "Successfully added the file " . HtmlUtil::getLink("file:" . FileUtil::sanitizeFile($this->getWsdlFile()), FileUtil::sanitizeFile($this->getWsdlFile()));
    }

    $viewName = "\\Backend\\View\\Api\\" . $this->_api . "View";
    $class = new ReflectionClass($viewName);
    $wsdlCode = FileUtil::get($this->getWsdlFile());
    $classCode = FileUtil::get($this->getViewFile());

    $functions = [];
    foreach ($class->getMethods(reflectionMethod::IS_PUBLIC) as $function) {
      if ("\\" . $function->class == $viewName) {
        $functions[] = $function->name;
      }
    }

    foreach ($functions as $function) {
      if (preg_match("/function {$function}\\(/", $wsdlCode)) {
        //method already defined.. skip to next file.
        continue;
      }
      $startLine = false;
      $endLine = false;
      $lines = explode("\n", $classCode);
      $functionCode = "";
      foreach ($lines as $lineNumber => $code) {
        if (strpos($code, "function {$function}(") !== false) {
          $startLine = $lineNumber;
        }
        if ($startLine && $startLine != $lineNumber) {
          if (preg_match("/function [^\\(]*\\(/", $code)) {
            $endLine = $lineNumber;

            break;
          }
          $functionCode .= $code . "\n";
        }
      }
      if (!$endLine) {
        $endLine = sizeof($lines);
      }
      //scrape some info from the function code
      //look for request type hints
      $requestTypes = [];
      if (preg_match("/this->isGet\\(\\)/", $functionCode)) {
        $requestTypes[] = "get";
      }
      if (preg_match("/this->isPost\\(\\)/", $functionCode)) {
        $requestTypes[] = "post";
      }
      if (preg_match("/this->isPut\\(\\)/", $functionCode)) {
        $requestTypes[] = "put";
      }
      if (preg_match("/this->isDelete\\(\\)/", $functionCode)) {
        $requestTypes[] = "delete";
      }
      //if didnt fine any request type hints assume its a post
      if (!$requestTypes) {
        $requestTypes = ["post"];
      }
      //look for params i.e. this->get("state")
      preg_match_all("/this->get\\(['\"](\\w+)['\"]\\)/", $functionCode, $matches);
      $getParams = array_unique(value($matches, 1, []));
      preg_match_all("/this->post\\(['\"](\\w+)['\"]\\)/", $functionCode, $matches);
      $postParams = value($matches, 1, []);
      preg_match_all("/this->request\\(['\"](\\w+)['\"]\\)/", $functionCode, $matches);
      $requestParams = array_unique(value($matches, 1, []));
      //look for fills
      preg_match_all("/->fill\\([^,]+, *\\[([^\\]]+)\\]/", $functionCode, $matches);
      $paramGroups = [];
      foreach (value($matches, 1, []) as $match) {
        preg_match_all("/['\"]([^'\"]+)['\"]/", $match, $pMatches);
        if (isset($pMatches[1])) {
          $group = $pMatches[1];
          sort($group);
          $paramGroups[] = array_unique($group);
          $postParams = array_merge($postParams, $group);
        }
      }
      //look for returns
      preg_match_all("/this->data\\(['\"]([^'\"]+)['\"]\\W*,/", $functionCode, $matches);
      $returns = array_unique(value($matches, 1, []));
      //start generating wsdl code
      $methods = [];
      foreach ($requestTypes as $type) {
        $params = $requestParams;
        if ($type == "get") {
          $params = array_merge($params, $getParams, ["limit", "order", "offset", "page"]);
        } else {
          $params = array_merge($params, $postParams);
        }
        $params = array_unique($params);
        sort($params);
        //make a guess at return model so that we can guess param constants i.e. states
        $modelName = false;
        if ($returns) {
          foreach ($returns as $return) {
            $singleReturn = substr($return, -1) == "s" ? substr($return, 0, -1) : $return;
            $potentialModelName = "\\Backend\\Model\\" . ucfirst($singleReturn) . "Model";
            if (class_exists($potentialModelName)) {
              $modelName = $potentialModelName;

              break;
            }
          }
        }
        $method = "      Method::create(\"{$type}\")\n";
        if ($type != "delete") {
          if ($params) {
            $method .= "        ->params(" . $this->getParamList($params, $modelName) . ")\n";
          }
          if (in_array($type, ["post", "put"]) && $paramGroups) {
            foreach ($paramGroups as $idx => $group) {
              $method .= "        ->paramGroup(\"group{$idx}\", " . $this->getParamList($group, $modelName, false) . ")\n";
            }
          }
        }
        if ($returns) {
          foreach ($returns as $return) {
            $type = "null";
            $singleReturn = substr($return, -1) == "s" ? substr($return, 0, -1) : $return;
            $modelName = "\\Backend\\Model\\" . ucfirst($singleReturn) . "Model";
            if (class_exists($modelName)) {
              $type = $modelName . "::class";
            }
            if (substr($return, -1) == "s") {
              $method .= "        ->returnArray(\"{$return}\", {$type})\n";
            } else {
              $method .= "        ->return(\"{$return}\", {$type})\n";
            }
          }
        }
        $methods[] = $method;
      }
      $functionCode = $this->assign("function", $function)
        ->assign("methods", implode("      ,\n\n", $methods))
        ->fetch(PathModel::getAssetsDirectory() . "wsdl_method.inc");
      //add function to end of wsdl class
      $pos = strrpos($wsdlCode, "}");
      if ($pos === false) {
        throw new Exception("There was a problem trying to located the end of the class");
      }
      $wsdlCode = substr_replace($wsdlCode, $functionCode, $pos, 0);
      $messages[] = "Added the {$method}() to " . HtmlUtil::getLink("file:" . FileUtil::sanitizeFile($this->getWsdlFile()), FileUtil::sanitizeFile($this->getWsdlFile()));
    }

    FileUtil::put($this->getWsdlFile(), $wsdlCode);

    //update RouteManager with wsdl class if not already defined.
    $routeManagerCode = FileUtil::get($this->getRouteMangerFile());
    if (!preg_match("/{$this->_api}Wsdl::class/", $routeManagerCode)) {
      $routeManagerCode = preg_replace("/( *)\"class\"\\W*=>\\W*\\\\Backend\\\\View\\\\Api\\\\{$this->_api}View::class\\W*,/", "\\1\"class\" => \\Backend\\View\\Api\\{$this->_api}View::class,\n\\1\"wsdl\" => \\Backend\\View\\Api\\Wsdl\\{$this->_api}Wsdl::class,", $routeManagerCode);
      FileUtil::put($this->getRouteMangerFile(), $routeManagerCode);
    }

    return true;
  }

  public function getWsdlFile() {
    return $this->getInstanceDir() . "View/Api/Wsdl/" . str_replace("_", "", $this->_api) . "Wsdl.php";
  }

  public function getViewFile() {
    return $this->getInstanceDir() . "View/Api/" . str_replace("_", "", $this->_api) . "View.php";
  }

  private function getParamList($params, $modelName, $includeTypes = true) {
    $list = "[";
    foreach ($params as $idx => $param) {
      if ($includeTypes) {
        if (substr($param, -3) === "_id") {
          $list .= "\n          \"{$param}\"=>[\"type\"=>\"int\"]";
        } elseif ($modelName && method_exists($modelName, "get_{$param}s")) {
          $list .= "\n          \"{$param}\"=>[\"type\"=>array_keys({$modelName}::get_{$param}s())]";
        } else {
          $list .= "\n          \"{$param}\"";
        }
      } else {
        $list .= "\"{$param}\"";
      }
      if ($idx + 1 < sizeof($params)) {
        $list .= ", ";
      } elseif ($includeTypes) {
        $list .= "\n        ";
      }
    }
    $list .= "]";

    return $list;
  }

  public function getRouteMangerFile() {
    return $this->getInstanceDir() . "Manager/RouteManager.php";
  }
}
