<?php

namespace Utility\Model;

use Exception;

class CMODEL_VA_GENERATOR_VIEW_UPDATE extends CMODEL_VA_GENERATOR_VIEW {

  protected $_model       = "";
  protected $_format      = "";
  protected $_relation_field  = "";

  function __construct($controller, $task, $task_plural, $format, $security_roles, $app_dir, $relation_field) {
    parent::__construct($controller, $task, $task_plural, $security_roles, $app_dir);
    $this->_relation_field = $relation_field;
    $this->_format = $format;
  }

  function generate() {

    $dbo     = "DBO_" . strtoupper($this->_model);
    $cmodel   = "CMODEL_" . strtoupper($this->_model);

    if (!class_exists($dbo))
      throw new Exception("The " . $dbo . " DBO does not exist");

    $dbos = $cmodel::create()->get_dbos();

    $primary_dbo = end($dbos);
    reset($dbos);

    $primary_key = $primary_dbo ? value(array_keys($primary_dbo->get_primary_keys()), 0) : null;

    $columns = $dependent_objects = array();
    foreach ($dbos as $dbo) {

      foreach ($dbo->get_columns() as $name => $column) {

        $column->label = $this->get_pretty($name, true);

        if (!$column->is_primary() && !in_array($name, array("guid", "create_date", "modify_date", "priority"))) {

          if (preg_match("/(.*?)_id$/", $name, $matches)) {

            $dependent_object = value($matches, 1);

            $dependent_columns = MODEL_DB::get_dbo_columns($dependent_object, $this->_app_dir);

            $dependent_column = array_key_exists("name", $dependent_columns) ? "name" : $dependent_object . "_id";

            $dependent_objects[$dependent_object] = $dependent_column;
          } else {
            $columns[$name] = $column;
          }
        }
      }
    }

    $relation_field_name = preg_replace("/_id$/", "", $this->_relation_field);

    $title           = $this->get_pretty($this->_model);
    $pretty_model       = $this->get_pretty($this->_model, false);
    $pretty_relation_field   = $this->get_pretty($relation_field_name, false);


    $this->_smarty->assign("pretty_model", $pretty_model);
    $this->_smarty->assign("plural_pretty_model", LANG_UTIL::get_plural($pretty_model));
    $this->_smarty->assign("lower_model", strtolower($this->_model));
    $this->_smarty->assign("hyphen_model", self::get_hyphen($this->_model));
    $this->_smarty->assign("has_name", array_key_exists("name", $columns));
    $this->_smarty->assign("upper_model", strtoupper($this->_model));
    $this->_smarty->assign("model", $this->_model);
    $this->_smarty->assign("lower_models", LANG_UTIL::get_plural_string(strtolower($this->_model)));
    $this->_smarty->assign("id", $this->get_short_key_field_name($this->_model));
    $this->_smarty->assign("relation_field_abr", self::get_abr($this->_relation_field));
    $this->_smarty->assign("relation_field", $this->_relation_field);
    $this->_smarty->assign("relation_field_controller", str_replace("_", "", $relation_field_name));
    $this->_smarty->assign("pretty_relation_field", $pretty_relation_field);
    $this->_smarty->assign("plural_relation_field", $relation_field_name);
    $this->_smarty->assign("columns", $columns);
    $this->_smarty->assign("title", $title);
    $this->_smarty->assign("primary_key", $primary_key);
    $this->_smarty->assign("dependent_objects", $dependent_objects);
    $this->_smarty->assign("is_interface_popup", $this->_format == "U");
    $this->_smarty->assign("is_interface_blank", $this->_format == "L");

    parent::generate();
  }

  function get_template_filename() {
    return "update_" . get_value(array("A" => "ajax", "P" => "post"), $this->_method) . "_view.inc";
  }
  function get_template_template_filename() {
    return "update_" . get_value(array("A" => "ajax", "P" => "post"), $this->_method) . "_template.inc";
  }

  function set_model($model) {
    $this->_model = $model;
  }
}
