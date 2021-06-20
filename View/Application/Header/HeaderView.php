<?php

namespace Utility\View\Application\Header;

use Framework\Core\View;


class HeaderView extends View {

  public function __construct() {
    $this->setTemplate("./HeaderTemplate.php");
    $this->disableAuthorization();
  }

  public function init() {
  }

}
