<?php

namespace Utility\View\Application\NotFound;

use Framework\Core\View;


class NotfoundView extends View {

  public function __construct() {
    $this->setTemplate("./NotFoundTemplate.php")
      ->disableAuthorization();
  }

}
