<?php

namespace Utility\Model;

class CMODEL_VA_GENERATOR_VIEW_LIST extends CMODEL_VA_GENERATOR_VIEW {

  protected $_model        = "";
  protected $_format        = "";
  protected $_is_search_form    = "";
  protected $_view_format      = "";
  protected $_id_get_column    = "";
  protected $_first_get_column  = "";
  protected $_id_column      = "";
  protected $_relation_field    = "";
  protected $_list_body      = "";
  protected $_view_method      = "";

  function __construct($controller, $task, $task_plural, $format, $security_roles, $app_dir, $is_search_form, $view_format, $relation_field) {
    parent::__construct($controller, $task, $task_plural, $security_roles, $app_dir);
    $this->_format = $format;
    $this->_view_format = $view_format;
    $this->_is_search_form = $is_search_form;
    $this->_relation_field = $relation_field;
  }

  function pre_generate() {

    $id = $this->get_id();

    $columns = $this->get_dbo_columns();

    $headings = $get_functions = array();
    foreach ($columns as $name => $column) {

      if (!in_array($name, array("guid", "create_date", "modified_date", "priority"))) {

        $heading = $column->is_primary() ? "ID" : $this->get_pretty($name);

        $get_function = "\$" . $this->_model . "->get_" . $name . "()";

        if ($column->is_primary()) {

          if (array_key_exists("name", $columns))
            continue;

          $this->_id_get_column = $get_function;
          $this->_id_column = $name;
          $get_function = "\$name";
          $this->_first_get_column = $get_function;
        } elseif ($name == "state") {
          $get_function = "\$" . $this->_model . "->get_state_name()";
        } elseif ($name == "name") {
          $this->_id_get_column = $get_function;
          $this->_id_column = $name;
          $get_function = "\$name";
        }

        $get_functions[]   = $get_function;
        $headings[]     = '"' . $heading . '"';
      }
    }

    $this->_smarty->assign("relation_field_abr", self::get_abr($this->_relation_field));
    $this->_smarty->assign("relation_field", $this->_relation_field);
    $this->_smarty->assign("has_column_name", array_key_exists("name", $columns));
    $this->_smarty->assign("is_list_body_blank", $this->_list_body == "L");
    $this->_smarty->assign("is_list_body_popup", $this->_list_body == "U");
    $this->_smarty->assign("pretty_model", $this->get_pretty($this->_model, false));
    $this->_smarty->assign("pretty_plural_model", $this->get_pretty(LANG_UTIL::get_plural($this->_model, false)));
    $this->_smarty->assign("has_column_description", array_key_exists("description", $columns));
    $this->_smarty->assign("has_state", array_key_exists("state", $columns));
    $this->_smarty->assign("has_name", array_key_exists("has_name", $columns));
    $this->_smarty->assign("has_priority", array_key_exists("priority", $columns));
    $this->_smarty->assign("lower_model", $this->get_lower_model());
    $this->_smarty->assign("upper_model", $this->get_upper_model());
    $this->_smarty->assign("hyphen_model", self::get_hyphen($this->_model));
    $this->_smarty->assign("id", $this->get_short_key_field_name($this->_model));
    $this->_smarty->assign("title", $this->get_pretty($this->_model));
    $this->_smarty->assign("get_functions", implode(",", $get_functions));
    $this->_smarty->assign("headings", implode(",", $headings));
    $this->_smarty->assign("lower_model_condensed", strtolower(str_replace("_", "", $this->_model)));
    $this->_smarty->assign("lower_models", LANG_UTIL::get_plural_string(strtolower($this->_model)));
    $this->_smarty->assign("is_view_format_inline", $this->is_view_format_inline());
    $this->_smarty->assign("is_view_format_skip", $this->is_view_format_skip());
    $this->_smarty->assign("is_view_format_popup", $this->is_view_format_popup());
    $this->_smarty->assign("is_view_format_page", $this->is_view_format_page());
    $this->_smarty->assign("is_view_format_blank", $this->is_view_format_blank());
    $this->_smarty->assign("is_format_post", $this->_format == "P");
    $this->_smarty->assign("is_format_ajax", $this->_format == "A");
    $this->_smarty->assign("is_search_form", $this->_is_search_form);
    $this->_smarty->assign("id_get_column", $this->_id_get_column);
    $this->_smarty->assign("id_column", $this->_id_column);
  }

  function get_dbo_columns() {
    $dbo_object = MODEL_DB::get_dbo($this->_model, $this->_app_dir);
    return $dbo_object->get_columns();
  }

  function get_id() {
    return $this->get_short_key_field_name($this->_model);
  }
  function is_view_format_skip() {
    return $this->_view_format == "S";
  }
  function is_view_format_inline() {
    return $this->_view_format == "I";
  }
  function is_view_format_page() {
    return $this->_view_format == "P";
  }
  function is_view_format_blank() {
    return $this->_view_format == "L";
  }
  function is_view_format_popup() {
    return $this->_view_format == "U";
  }

  function get_lower_model() {
    return strtolower($this->_model);
  }
  function get_upper_model() {
    return strtoupper($this->_model);
  }

  function get_template_filename() {
    return MODEL_GENERATE::get_list_view_template($this->_format);
  }
  function get_template_template_filename() {
    return MODEL_GENERATE::get_list_template_template($this->_format);
  }
  function get_view_filename() {
    return $this->_lower_task_plural . "_view.inc";
  }
  function get_view_template_filename() {
    return $this->_lower_task_plural . ".php";
  }

  function set_model($model) {
    $this->_model = $model;
  }
  function set_list_body($list_body) {
    $this->_list_body = $list_body;
  }
  function set_view_format($view_format) {
    $this->_view_format = $view_format;
  }
  function set_view_method($value) {
    $this->_view_method = $value;
  }
}
