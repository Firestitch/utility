<?php

namespace Utility\View\Wsdl;

use Exception;
use Framework\Api\ApiResponse;
use Framework\Core\View;
use Framework\Core\WebApplication;
use Framework\Util\FileUtil;
use Utility\Model\WsdlGeneratorModel;


class WsdlView extends View {

  public function __construct() {
    $this->disableAuthorization();
    $this->setTemplate("./WsdlTemplate.php");
    $this->setForm("javascript:;", false, "form-api");
  }

  public function init() {
    $this->processPost();
    $views = FileUtil::getDirectoryListing(WebApplication::getMainApplicationDirectory() . "View/api/");
    $apis = [];
    foreach ($views as $view) {
      $name = preg_replace("/View\\.php/", "", $view);
      $label = $name;
      if (file_exists(WebApplication::getMainApplicationDirectory() . "View/Api/Wsdl/" . $name . "Wsdl.php")) {
        $label .= " (existing)";
      }
      $apis[$name] = $label;
    }
    // $this->set_var("model", $this->get("model"));
    // $this->set_var("models", ModelGeneratorModel::get_cmodels());
    $this->setVar("apis", $apis);
  }

  public function processPost() {
    if ($this->isPost()) {
      try {
        $response = new ApiResponse();
        $dir = WebApplication::getMainApplicationDirectory();
        //$model       = $this->post("model");
        $api = $this->post("api");
        // $model_plural  = $this->post("model-plural");
        $options = (array)$this->post("options");
        // $methods    = (array)$this->post("methods");
        // $method     = $this->post("method");
        // if (!$model)
        //   throw new Exception("Invalid model");
        // if (!$model_plural)
        //   throw new Exception("Plural model");
        $messages = [];
        if ($api) {
          (new WsdlGeneratorModel($dir, $api, $options))->generate($messages);
        } else {
          // (new WsdlGeneratorModel(
          //   $dir,
          //   $model_plural,
          //   "",
          //   $options
          // ))
          //   ->generate(in_array("override", $options), $messages);
        }
        $response->success();
        WebApplication::addNotify("Successfully generated WSDL");
      } catch (Exception $e) {
        WebApplication::addError($e->getMessage());
        $response->data("errors", WebApplication::getErrorMessages());
      }

      $response
        ->data("warnings", WebApplication::getWarningMessages())
        ->data("messages", WebApplication::getNotifyMessages())
        ->render();
    }
  }

}