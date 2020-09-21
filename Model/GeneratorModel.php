<?

namespace Utility\Model;

use Framework\Model\SmartyModel;
use Framework\Util\FILE_UTIL;

class GeneratorModel {

  protected $_instance_dir   = "";
  protected $_smarty_cmodel   = "";

  function __construct($instance_dir) {
    $this->_instance_dir = $instance_dir;

    $this->_smarty_cmodel = new SmartyModel();
    $this->_smarty_cmodel->allow_php_tag();
    $this->_smarty_cmodel->disableSecurity();
  }

  function get_instance_dir() {
    return $this->_instance_dir;
  }

  function write_template($template, $file) {
    $content = $this->_smarty_cmodel->fetch($template);
    $this->write($file, $content);
    return $this;
  }

  function write($file, $string) {
    FILE_UTIL::put($file, $string);
    return $this;
  }

  function assign($name, $value) {
    $this->_smarty_cmodel->assign($name, $value);
    return $this->_smarty_cmodel;
  }

  function register_autoload() {
    spl_autoload_register(array($this, "autoload"), true, true);
  }

  function create_cmodel($model) {
    $cmodel_class = "CMODEL_" . strtoupper($model);
    return $cmodel_class::create();
  }

  function autoload($class) {

    if (preg_match("/CMODEL_/", $class)) {
      return APPLICATION::include_model($class, "complex", $this->_instance_dir, array("framework" => false, "trigger" => false));
    }

    if (preg_match("/DBO_/", $class)) {
      return APPLICATION::include_dbo($class, $this->_instance_dir, array("framework" => false, "trigger" => false));
    }

    if (preg_match("/DBQ_/", $class)) {
      return APPLICATION::include_dbq($class, $this->_instance_dir, array("framework" => false, "trigger" => false));
    }
  }

  static function get_locations() {

    $dirs = FILE_UTIL::get_directory_listing(DIR_INSTANCE);

    $applications = array();

    foreach ($dirs as $dir)
      if (is_file(DIR_INSTANCE . $dir . "/system/managers/system_manager.inc"))
        $applications[$dir] = $dir;

    $applications["framework"] = "framework";

    return $applications;
  }
}
