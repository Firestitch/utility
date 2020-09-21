<?

namespace Utility\Model;


class CMODEL_VA_GENERATOR_ACTION extends CMODEL_VA_GENERATOR {

  protected $_model    = "";
  protected $_template_filename   = "default_action.inc";

  function __construct($controller, $task, $security_roles, $app_dir) {
    parent::__construct($controller, $task, "", $security_roles, $app_dir);
  }

  function get_template_filename() {
    return $this->_template_filename;
  }

  function get_action_directory() {
    return $this->_app_dir . "actions/" . $this->_lower_controller . "/";
  }
  function get_action_filename() {
    return $this->_lower_task . "_action.inc";
  }
  function get_action_file() {
    return $this->get_action_directory() . $this->get_action_filename();
  }



  function has_action_file() {
    return is_file($this->get_action_file());
  }

  function generate(&$messages = array()) {

    $this->_smarty->assign("lower_model", strtolower($this->_model));

    FILE_UTIL::mkdir($this->get_action_directory());

    $template_file = MODEL_PATH::get_assets_directory() . $this->get_template_filename();

    $content = $this->_smarty->fetch($template_file);

    $this->write_file($this->get_action_file(), $content);

    $messages[] = "Successfully added the file " . HTML_UTIL::get_link("file:" . $this->get_action_file(), $this->get_action_file());
  }

  function set_model($model) {
    $this->_model = $model;
  }
}
