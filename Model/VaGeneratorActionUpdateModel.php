<?php

namespace Utility\Model;

class CMODEL_VA_GENERATOR_ACTION_UPDATE extends CMODEL_VA_GENERATOR_ACTION {

  protected $_format       = "";
  protected $_model      = "";
  protected $_relation_field  = "";
  protected $_method      = "";

  function __construct($controller, $task, $format, $security_roles, $app_dir, $relation_field) {
    parent::__construct($controller, $task, $security_roles, $app_dir);
    $this->_format = $format;
    $this->_relation_field = $relation_field;
  }

  function set_method($method) {
    $this->_method = $method;
  }
  function is_view_format_skip() {
    return $this->_format == "S";
  }
  function is_view_format_ajax() {
    return $this->_format == "A";
  }
  function is_view_format_page() {
    return $this->_format == "P";
  }
  function is_view_format_popup() {
    return $this->_format == "U";
  }

  function generate(&$messages = array()) {

    $this->_smarty->assign("is_framework", preg_match("/framework/i", $this->_app_dir));
    $this->_smarty->assign("is_view_format_ajax", $this->is_view_format_ajax());
    $this->_smarty->assign("is_view_format_skip", $this->is_view_format_skip());
    $this->_smarty->assign("is_view_format_popup", $this->is_view_format_popup());
    $this->_smarty->assign("is_view_format_page", $this->is_view_format_page());
    $this->_smarty->assign("relation_field_abr", self::get_abr($this->_relation_field));
    $this->_smarty->assign("relation_field", $this->_relation_field);
    $this->_smarty->assign("pretty_model", $this->get_pretty($this->_model, false));
    $this->_smarty->assign("upper_model", strtoupper($this->_model));
    $this->_smarty->assign("upper_model", strtoupper($this->_model));
    $this->_smarty->assign("lower_models", LANG_UTIL::get_plural_string(strtolower($this->_model)));
    $this->_smarty->assign("id", $this->get_short_key_field_name($this->_model));

    parent::generate($messages);
  }

  function get_template_filename() {
    return MODEL_GENERATE::get_update_action_template($this->_method);
  }
  function set_model($model) {
    $this->_model = $model;
  }
}
