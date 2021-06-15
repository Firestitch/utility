<?php

namespace Utility\View\Application\Body;

use Utility\View\Application\Header\HeaderView;

class BodyView extends \Framework\View\Application\Body\BodyView {
  public function __construct() {
    parent::__construct();
    $this
      ->setTemplate("./BodyTemplate.php")
      ->setView("header", new HeaderView())
      ->disableAuthorization();
  }

  public function init() {
    parent::init();
    self::addWebAssets($this->getWebAssetManager());
    $this->setVar("webAssetManager", $this->getWebAssetManager());
    $this->setVar("self", $this);
  }

  public static function addWebAssets($webAssetManagerModel) {
    $webAssetManagerModel->addJsUrl("//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js")->addJsUrl("//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js")->addJsUrl("//ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js")->addCssUrl("//netdna.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css")->addCssUrl("//fonts.googleapis.com/css?family=Open+Sans")->addJsLib("common.js")->addJsApp("global.js")->addCssLib("base.css")->addCssApp("styles.css");
  }
}
