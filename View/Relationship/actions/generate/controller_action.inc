<?
	class ACTION_GENERATE_CONTROLLER extends ACTION_JSON {

		function __construct() {
			parent::__construct()->disable_authorization();
		}

		function process() {

			$controller				= $this->post("controller");
			$controller_template	= DIR_INSTANCE."utility/assets/frontend_controller.inc";
			$index_file				= DIR_INSTANCE."frontend/app/index.html";
			$app_dir				= DIR_INSTANCE."frontend/app/";

			$this->_smarty = CMODEL_SMARTY::create()
								->assign("controller",$controller)
								->assign("object","")
								->assign("interface","")
								->assign("view_format","")
								->assign("options",[]);

			$index_html = FILE_UTIL::get($index_file);

			if(!$index_html)
				throw new Exception("Failed to locate index.html");

			if(stripos($index_html,"<!-- endcontrollers -->")===false)
				throw new Exception("Failed to locate <&#33;-- endcontrollers --> index.html");

			$js_include = '<script src="scripts/controllers/'.strtolower($controller).'.js"></script>';

			if(stripos($index_html,$js_include)===false) {
				$index_html = str_replace("<!-- endcontrollers -->",$js_include."\n\t\t<!-- endcontrollers -->",$index_html);
				FILE_UTIL::put($index_file,$index_html);
			}

			FILE_UTIL::put($app_dir."scripts/controllers/".strtolower($controller).".js",$this->_smarty->fetch_string(FILE_UTIL::get($controller_template)));

			$this->success();
		}
	}
