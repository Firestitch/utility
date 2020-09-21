<?

namespace Utility\Model;

class CMODEL_VA_GENERATOR_ACTION_REMOVE extends CMODEL_VA_GENERATOR_ACTION {

  protected $_model   = "";
  protected $_format  = "";

  function __construct($controller, $task, $format, $security_roles, $app_dir) {
    parent::__construct($controller, $task, $security_roles, $app_dir);
    $this->_format = $format;
  }

  function generate(&$messages = array()) {

    $this->_smarty->assign("title", strtolower($this->get_pretty($this->_model, false)));
    $this->_smarty->assign("id", $this->get_short_key_field_name($this->_model));
    $this->_smarty->assign("lower_model", strtolower($this->_model));
    $this->_smarty->assign("upper_model", strtoupper($this->_model));
    $this->_smarty->assign("lower_models", LANG_UTIL::get_plural_string(strtolower($this->_model)));
    $this->_smarty->assign("is_format_post", $this->_format == "P");

    parent::generate($messages);
  }

  function get_template_filename() {
    return MODEL_GENERATE::get_remove_action_template($this->_format);
  }
  function get_action_filename() {
    return $this->_lower_task . "remove_action.inc";
  }
  function set_model($model) {
    $this->_model = $model;
  }
}
