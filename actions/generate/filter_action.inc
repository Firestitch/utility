<?
	class ACTION_GENERATE_FILTER extends ACTION_JSON {

		function __construct() {
			parent::__construct()->disable_authorization();
		}

		function process() {

			$filter					= $this->post("filter");
			$filter_template		= DIR_INSTANCE."utility/assets/frontend_filter.inc";
			$index_file				= DIR_INSTANCE."frontend/app/index.html";
			$app_dir				= DIR_INSTANCE."frontend/app/";

			$this->_smarty = new CMODEL_SMARTY();
			$this->_smarty->assign("filter",$filter);

			try {

				$index_html = FILE_UTIL::get($index_file);

				if(!$index_html)
					throw new Exception("Failed to locate index.html");

				if(stripos($index_html,"<!-- endfilters -->")===false)
					throw new Exception("Failed to locate <&#33;-- endfilters --> index.html");

				$js_include = '<script src="scripts/filters/'.strtolower($filter).'.js"></script>';

				if(stripos($index_html,$js_include)===false) {
					$index_html = str_replace("<!-- endfilters -->",$js_include."\n\t\t<!-- endfilters -->",$index_html);
					FILE_UTIL::put($index_file,$index_html);
				}

				FILE_UTIL::mkdir($app_dir."scripts/filters");
				FILE_UTIL::put($app_dir."scripts/filters/".strtolower($filter).".js",$this->_smarty->fetch_string(FILE_UTIL::get($filter_template)));

				$this->success();

			} catch(Exception $e) {
				$this->error($e);
			}
		}
	}
