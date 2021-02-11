<?php

namespace Utility\View\Wsdl;

use Exception;
use Framework\Core\View;
use Framework\Core\WebApplication;
use Framework\Api\ApiResponse;
use Framework\Util\FILE_UTIL;
use Utility\Model\WsdlGeneratorModel;

class WsdlView extends View {

  public function __construct() {
    $this->disable_authorization();
    $this->set_template("./WsdlTemplate.php");
    $this->set_form("javascript:;", false, "form-api");
  }

  public function init() {

    $this->process_post();

    $views = FILE_UTIL::get_directory_listing(WebApplication::get_main_application_directory() . "View/api/");

    $apis = [];
    foreach ($views as $view) {
      $name = preg_replace("/View\.php/", "", $view);
      $label = $name;

      if(file_exists(WebApplication::get_main_application_directory() . "View/Api/Wsdl/".$name."Wsdl.php"))
        $label .= " (existing)";

      $apis[$name] = $label;
    }

    // $this->set_var("model", $this->get("model"));
    // $this->set_var("models", ModelGeneratorModel::get_cmodels());
    $this->set_var("apis", $apis);
  }

  public function process_post() {

    if ($this->is_post()) {

      try {

        $response = new ApiResponse();

        $dir       = WebApplication::get_main_application_directory();
        //$model       = $this->post("model");
        $api       = $this->post("api");
        // $model_plural  = $this->post("model-plural");
        $options    = (array)$this->post("options");
        // $methods    = (array)$this->post("methods");
        // $method     = $this->post("method");

        // if (!$model)
        //   throw new Exception("Invalid model");

        // if (!$model_plural)
        //   throw new Exception("Plural model");

        $messages = [];

        if ($api) {

          (new WsdlGeneratorModel(
            $dir,
            $api,
            $options
          ))
            ->generate($messages);
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

        WebApplication::add_notify("Successfully generated WSDL");
      } catch (Exception $e) {
        WebApplication::add_error($e->getMessage());
        $response->data("errors", WebApplication::get_error_messages());
      }

      $response
        ->data("warnings", WebApplication::get_warning_messages())
        ->data("messages", WebApplication::get_notify_messages())
        ->render();
    }
  }
}
