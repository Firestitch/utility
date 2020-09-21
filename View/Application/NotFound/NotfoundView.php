<?php

namespace Utility\View\Application\NotFound;

use Framework\Core\View;

class NotfoundView extends View {

  function __construct() {

    $this
      ->set_template("./NotFoundTemplate.php")
      ->disable_authorization();
  }
}
