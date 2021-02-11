<?php

namespace Utility\Model;

use Exception;
use Framework\Util\ARRAY_UTIL;
use Framework\Util\FILE_UTIL;
use Framework\Util\HTML_UTIL;
use Framework\Util\LANG_UTIL;
use Framework\Util\STRING_UTIL;
use ReflectionClass;
use ReflectionMethod;

class WsdlGeneratorModel extends GeneratorModel {

  protected $_dir          = "";
  protected $_options        = [];

  function __construct($dir, $api, $options = []) {
    parent::__construct($dir);

    $this->_options = $options;
    $this->_api = $api;
  }

  function get_view_file() {
    return $this->get_instance_dir()."View/Api/".str_replace("_", "", $this->_api) . "View.php";
  }

  function get_wsdl_file() {
    return $this->get_instance_dir()."View/Api/Wsdl/" . str_replace("_", "", $this->_api) . "Wsdl.php";
  }

  function get_route_manger_file() {
    return $this->get_instance_dir()."Manager/RouteManager.php";
  }


  function generate($override, &$messages = array()) {

    if (!is_file($this->get_wsdl_file())) {
      $this->assign("api", $this->_api);

      if (!$this->write_template(PathModel::get_assets_directory() . "wsdl.inc", $this->get_wsdl_file()))
        throw new Exception("Failed to generate " . $this->get_wsdl_file());

      $messages[] = "Successfully added the file " . HTML_UTIL::get_link("file:" . FILE_UTIL::sanitize_file($this->get_wsdl_file()), FILE_UTIL::sanitize_file($this->get_wsdl_file()));
    }

    $view_name = "\\Backend\\View\\Api\\".$this->_api."View";

    $class = new ReflectionClass($view_name);
    $methods = [];
    foreach($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
      if("\\".$method->class == $view_name)
        $methods[] = $method->name;
    };


    $wsdl_code = FILE_UTIL::get($this->get_wsdl_file());
    $class_code = FILE_UTIL::get($this->get_view_file());


    foreach($methods as $method) {

      if(preg_match("/function {$method}\(/", $wsdl_code)) {
        //method already defined.. skip to next file.
        continue;
      }


      $start_line = false;
      $end_line = false;

      $lines = explode("\n", $class_code);
      $function_code = "";
      foreach($lines as $line_number=>$code) {
        if(strpos($code, "function {$method}(")!== false) {
          $start_line = $line_number;
        }
        if($start_line && $start_line!=$line_number) {
          if(preg_match("/function [^\(]*\(/", $code)) {
            $end_line = $line_number;
            break;
          }

          $function_code .= $code."\n";
        }
      }
      if(!$end_line)
        $end_line = sizeof($lines);

      //scrape some info from the function code

      //look for request type hints
      $request_types = [];
      if(preg_match("/this->is_get\(\)/",$function_code))
        $request_types[] = "get";
      if(preg_match("/this->is_post\(\)/",$function_code))
        $request_types[] = "post";
      if(preg_match("/this->is_put\(\)/",$function_code))
        $request_types[] = "put";
      if(preg_match("/this->is_delete\(\)/",$function_code))
        $request_types[] = "delete";

      //if didnt fine any request type hints assume its a post
      if(!$request_types)
        $request_types = ["post"];


      //look for params i.e. this->get("state")
      preg_match_all("/this->get\(['\"](\w+)['\"]\)/", $function_code, $matches);
      $get_params = array_unique(value($matches,1,[]));
      preg_match_all("/this->post\(['\"](\w+)['\"]\)/", $function_code, $matches);
      $post_params = value($matches,1,[]);
      preg_match_all("/this->request\(['\"](\w+)['\"]\)/", $function_code, $matches);
      $request_params = array_unique(value($matches,1,[]));

      //look for fills
      preg_match_all("/->fill\([^,]+, *\[([^\]]+)\]/", $function_code, $matches);
      $param_groups = [];
      foreach(value($matches,1,[]) as $match) {
        preg_match_all("/['\"]([^'\"]+)['\"]/", $match, $p_matches);
        if(isset($p_matches[1])) {
          $group = $p_matches[1];
          sort($group);
          $param_groups[] = array_unique($group);
          $post_params = array_merge($post_params, $group);
        }
      }


      //look for returns
      preg_match_all("/this->data\(['\"]([^'\"]+)['\"]\W*,/", $function_code, $matches);
      $returns = array_unique(value($matches,1,[]));


      //start generating wsdl code
      $endpoints = [];
      foreach($request_types as $type) {

        $params = $request_params;
        if($type=="get")
          $params = array_merge($params,$get_params);
        else
          $params = array_merge($params,$post_params);

        $params = array_unique($params);
        sort($params);


        //make a guess at return model so that we can guess param constants i.e. states
        $model_name = false;
        if($returns) {
          foreach($returns as $return) {
            $single_return = substr($return, -1)=="s" ? substr($return, 0, -1) : $return;
            $potential_model_name = "\Backend\Model\\".ucfirst($single_return)."Model";
            if(class_exists($potential_model_name)) {
              $model_name = $potential_model_name;
              break;
            }
          }
        }



        $endpoint = "      Endpoint::create(\"{$type}\")\n";
        if($type!="delete") {
          if($params) {
            $endpoint .= "        ->params(".$this->get_param_list($params, $model_name).")\n";
          }

          if(in_array($type, ["post","put"]) && $param_groups) {
            foreach($param_groups as $idx=>$group) {
              $endpoint .= "        ->param_group(\"group{$idx}\", ".$this->get_param_list($group, $model_name, false).")\n";
            }
          }
        }

        if($returns) {
          foreach($returns as $return) {
            $type = "null";

            $single_return = substr($return, -1)=="s" ? substr($return, 0, -1) : $return;
            $model_name = "Backend\Model\\".ucfirst($single_return)."Model";

            if(class_exists($model_name))
              $type = $model_name."::class";

            if(substr($return, -1)=="s") {
              $endpoint .= "        ->return_array(\"{$return}\", {$type})\n";
            } else {
              $endpoint .= "        ->return(\"{$return}\", {$type})\n";
            }
          }
        }

          $endpoints[] = $endpoint;
      }


      $function_code = $this
        ->assign("method", $method)
        ->assign("endpoints", implode("      ,\n",$endpoints))
      ->fetch(PathModel::get_assets_directory() . "wsdl_endpoint.inc");


      //add function to end of wsdl class
      $pos = strrpos($wsdl_code, "}");
      if ($pos === false)
        throw new Exception("There was a problem trying to located the end of the class");

      $wsdl_code = substr_replace($wsdl_code, $function_code, $pos, 0);

      $messages[] = "Added the $method() to " . HTML_UTIL::get_link("file:" . FILE_UTIL::sanitize_file($this->get_wsdl_file()), FILE_UTIL::sanitize_file($this->get_wsdl_file()));

    }

    FILE_UTIL::put($this->get_wsdl_file(), $wsdl_code);



    //update RouteManager with wsdl class if not already defined.
    $route_manager_code = FILE_UTIL::get($this->get_route_manger_file());

    if (!preg_match("/{$this->_api}Wsdl::class/", $route_manager_code)) {
      $route_manager_code = preg_replace(
        "/( *)\"class\"\W*=>\W*\\\\Backend\\\\View\\\\Api\\\\{$this->_api}View::class\W*,/",
        "\\1\"class\" => \Backend\View\Api\\{$this->_api}View::class,\n\\1\"wsdl\" => \Backend\View\Api\Wsdl\\{$this->_api}Wsdl::class,",
        $route_manager_code
      );

      FILE_UTIL::put($this->get_route_manger_file(), $route_manager_code);
    }


    return true;
  }



  private function get_param_list($params, $model_name, $include_types=true) {
    $list = "[";
    foreach($params as $idx=>$param){
      if($include_types) {
        if(substr($param,-3)==="_id") {
          $list .= "\n          \"{$param}\"=>[\"type\"=>\"int\"]";
        } elseif($model_name && method_exists($model_name, "get_{$param}s")) {
          $list .= "\n          \"{$param}\"=>[\"type\"=>array_keys({$model_name}::get_{$param}s())]";
        } else {
          $list .= "\n          \"{$param}\"";
        }
      } else {
        $list .= "\"{$param}\"";
      }

      if($idx+1 < sizeof($params)) {
        $list .= ", ";
      } elseif($include_types) {
        $list .= "\n        ";
      }
    }
    $list .= "]";

    return $list;
  }
}
