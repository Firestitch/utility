<?

namespace Utility\Model;

class CMODEL_INSTANCE extends CMODEL {

  public static function get_cmodels($app_dir) {

    $listing = FILE_UTIL::get_directory_listing($app_dir . "models/complex/");

    $list = array();
    foreach ($listing as $item)
      if (preg_match("/(.*?)_complex_model\.inc/", $item, $matches))
        $list[value($matches, 1)] = value($matches, 1);

    return $list;
  }
}
