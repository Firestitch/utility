<?php

namespace Utility\Manager;

use Framework\Manager\SessionManagerBase;

class SessionManager extends SessionManagerBase {
  public static function getKeyName() {
    return "key";
  }
}
