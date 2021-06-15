<?php

namespace Utility\Model;

use Exception;
use Framework\Util\ArrayUtil;
use Framework\Util\FileUtil;
use Framework\Util\HtmlUtil;
use Framework\Util\LangUtil;
use Framework\Util\StringUtil;
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
  public function getViewFile() {
    return $this->getInstanceDir() . "View/Api/" . str_replace("_", "", $this->_api) . "View.php";
  }
  public function getWsdlFile() {
    return $this->getInstanceDir() . "View/Api/Wsdl/" . str_replace("_", "", $this->_api) . "Wsdl.php";
  }
  public function getRouteMangerFile() {
    return $this->getInstanceDir() . "Manager/RouteManager.php";
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
    $methods = [];
    foreach ($class->getMethods(reflectionMethod::IS_PUBLIC) as $method) {
      if ("\\" . $method->class == $viewName) {
        $methods[] = $method->name;
      }
    }
    $wsdlCode = FileUtil::get($this->getWsdlFile());
    $classCode = FileUtil::get($this->getViewFile());
    foreach ($methods as $method) {
      if (preg_match("/function {$method}\\(/", $wsdlCode)) {
        //method already defined.. skip to next file.
        continue;
      }
      $startLine = false;
      $endLine = false;
      $lines = explode("\n", $classCode);
      $functionCode = "";
      foreach ($lines as $lineNumber => $code) {
        if (strpos($code, "function {$method}(") !== false) {
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
      if (preg_match("/this->is_get\\(\\)/", $functionCode)) {
        $requestTypes[] = "get";
      }
      if (preg_match("/this->is_post\\(\\)/", $functionCode)) {
        $requestTypes[] = "post";
      }
      if (preg_match("/this->is_put\\(\\)/", $functionCode)) {
        $requestTypes[] = "put";
      }
      if (preg_match("/this->is_delete\\(\\)/", $functionCode)) {
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
      $endpoints = [];
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
        $endpoint = "      Endpoint::create(\"{$type}\")\n";
        if ($type != "delete") {
          if ($params) {
            $endpoint .= "        ->params(" . $this->getParamList($params, $modelName) . ")\n";
          }
          if (in_array($type, ["post", "put"]) && $paramGroups) {
            foreach ($paramGroups as $idx => $group) {
              $endpoint .= "        ->param_group(\"group{$idx}\", " . $this->getParamList($group, $modelName, false) . ")\n";
            }
          }
        }
        if ($returns) {
          foreach ($returns as $return) {
            $type = "null";
            $singleReturn = substr($return, -1) == "s" ? substr($return, 0, -1) : $return;
            $modelName = "Backend\\Model\\" . ucfirst($singleReturn) . "Model";
            if (class_exists($modelName)) {
              $type = $modelName . "::class";
            }
            if (substr($return, -1) == "s") {
              $endpoint .= "        ->return_array(\"{$return}\", {$type})\n";
            } else {
              $endpoint .= "        ->return(\"{$return}\", {$type})\n";
            }
          }
        }
        $endpoints[] = $endpoint;
      }
      $functionCode = $this->assign("method", $method)->assign("endpoints", implode("      ,\n", $endpoints))->fetch(PathModel::getAssetsDirectory() . "wsdl_endpoint.inc");
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
}
