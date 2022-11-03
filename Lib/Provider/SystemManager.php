<?php

namespace Utility\Lib\Provider;

use Framework\Email\Model\EmailModel;
use Framework\Email\Model\EmailSmtpModel;
use Framework\Manager\SystemManagerBase;
use Framework\Model\PathModel;
use Framework\Util\HtmlTableUtil;
use Framework\Util\HtmlUtil;
use Framework\View\Application\NotFound\NotFoundView;


class SystemManager extends SystemManagerBase {

  public static function getRestrictedView() {
    return null;
  }

  public static function hasAccess($roles = [], $permissions = []) {
    return false;
  }

  public static function initialize() {
    parent::initialize();

    HtmlUtil::setDefaults(
      [
        "input" => ["class" => "form-control"],
        "button" => ["class" => "btn btn-default"],
        "textarea" => ["class" => "form-control"],
        "dropdown" => ["class" => "form-control"]
      ]
    );

    HtmlTableUtil::setDefaults("table table-striped table-bordered", "", "");
  }

  public static function getNotFoundView() {
    return new NotFoundView();
  }


  public static function getLoginView() {
    return null;
  }

  public static function getTemporaryDirectory() {
    return PathModel::getTemporaryDirectory();
  }

  public static function requiresDbConnection() {
    return false;
  }

  public static function getTimezone() {
    return "UTC";
  }

  public static function getEmail($subject = "", $body = "", $fromEmail = "noreply@firestitch.com", $fromName = "noreply@firestitch.com"): EmailSmtpModel {
    return EmailModel::createSmtp($subject, $body, $fromEmail, $fromName)
      ->setSmtpHost("smtp.mandrillapp.com")
      ->setSmtpUser("admin@firestitch.com")
      ->setSmtpPass("OduzbNCeRE2aT4Q9fvqI7A")
      ->setSmtpPort(587);
  }
}