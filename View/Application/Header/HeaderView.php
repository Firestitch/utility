<?php

namespace Utility\View\Application\Header;

use Framework\Core\View;

class HeaderView extends View {

  function __construct() {
    $this->set_template("./HeaderTemplate.php");
    $this->disable_authorization();
  }

  function init() {
  }
}
