<?php

namespace Utility\View\Api;

use Exception;
use Framework\Api\ApiResponse;
use Framework\Core\View;
use Framework\Core\WebApplication;
use Utility\Model\ApiGeneratorModel;


class ApiApi extends View {

  public function __construct() {
    $this
      ->disableAuthorization();
  }

  public function init() {
    try {
      $response = new ApiResponse();
      $model = $this->post("model");
      $api = $this->post("api");
      $modelPlural = $this->post("model-plural");
      $options = (array)$this->post("options");
      $methods = (array)$this->post("methods");
      $method = $this->post("method");
      $namespace = $this->post("namespace");

      if (!$model) {
        throw new Exception("Invalid model");
      }

      if (!$modelPlural) {
        throw new Exception("Plural model");
      }

      $messages = [];
      if ($api) {
        $options["method"] = $method;
        (new ApiGeneratorModel($namespace, $api, $model, $modelPlural, $methods, rtrim($api, "s"), $options))
          ->append($messages);
      } else {
        (new ApiGeneratorModel($namespace, $modelPlural, $model, $modelPlural, $methods, "", $options))
          ->generate(in_array("override", $options), $messages);
      }

      $response->success();
      WebApplication::addNotify("Successfully generated API");
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