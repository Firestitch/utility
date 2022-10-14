<?php

namespace Utility\View\Refactor;

use Exception;
use Framework\Api\ApiResponse;
use Framework\Core\View;
use Framework\Core\WebApplication;
use Framework\File\FilePath;
use Utility\Model\ModelGeneratorModel;


class RefactorApiView extends View {

  public function __construct() {
    $this
      ->disableAuthorization();
  }

  public function init() {
    $response = new ApiResponse();

    $models = (array)$this->request("models");
    $namespace = $this->request("namespace");
    $dir = ModelGeneratorModel::getNamespaceDir($namespace);

    try {
      foreach ($models as $model) {
        $file = (new FilePath($dir));

        $file
          ->clone()
          ->appendDir("Model")
          ->setFile("{$model}Model.php")
          ->delete();

        $file
          ->clone()
          ->appendDir("Model/Traits")
          ->setFile("{$model}Trait.php")
          ->delete();

        $file
          ->clone()
          ->appendDir("Handler")
          ->setFile("{$model}Handler.php")
          ->delete();

        $file
          ->clone()
          ->appendDir("Dbo")
          ->setFile("{$model}Dbo.php")
          ->delete();

        $file
          ->clone()
          ->appendDir("Dbq")
          ->setFile("{$model}Dbq.php")
          ->delete();
      }
    } catch (Exception $e) {
      WebApplication::addError($e->getMessage());
    }

    $response
      ->data("warnings", WebApplication::getWarningMessages())
      ->data("messages", WebApplication::getNotifyMessages())
      ->render();
  }
}