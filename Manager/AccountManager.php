<?php

namespace Utility\Manager;

use Framework\Account\Manager\AccountManagerBase;
use Framework\Core\Handler;


class AccountManager extends AccountManagerBase {

  public static function getAccountHandler(): string {
    return Handler::class;
  }

  public static function getAccountModel(): string {
    return Handler::class;
  }
}
