<?php

namespace Utility\View\Api;

use Framework\Core\View;


class ApiView extends View {

  public function __construct() {
    $this
      ->setTemplate("./ApiTemplate.php")
      ->setStyle("./Api.scss")
      ->disableAuthorization()
      ->setForm("javascript:;", false, "form-api");
  }

  public function init() {
    $this->setVar("model", $this->get("model"));
  }
}
