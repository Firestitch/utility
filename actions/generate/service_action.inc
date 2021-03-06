<?
	class ACTION_GENERATE_SERVICE extends ACTION_JSON {

		function __construct() {
			parent::__construct()->disable_authorization();
		}

		function process() {

			$service				= str_replace("_","",$this->post("service"));
			$name					= $this->post("name");
			$plural_name			= $this->post("plural_name");
			$service_template		= DIR_INSTANCE."utility/assets/frontend_service.inc";
			$index_file				= DIR_INSTANCE."frontend/app/index.html";
			$app_dir				= DIR_INSTANCE."frontend/app/";

			$this->_smarty = CMODEL_SMARTY::create()
								->assign("service",$service)
								->assign("name",$name)
								->assign("plural_name",$plural_name)
								->assign("options",(array)$this->post("options"));
			try {


				$index_html = FILE_UTIL::get($index_file);

				if(!$index_html)
					throw new Exception("Failed to locate index.html");

				if(stripos($index_html,"<!-- endservices -->")===false)
					throw new Exception("Failed to locate <!-- endservices --> index.html");

				$js_include = '<script src="scripts/services/'.strtolower($service).'.js"></script>';

				if(stripos($index_html,$js_include)===false) {
					$index_html = str_replace("<!-- endservices -->",$js_include."\n\t<!-- endservices -->",$index_html);
					FILE_UTIL::put($index_file,$index_html);
				}

				FILE_UTIL::put($app_dir."scripts/services/".strtolower($service).".js",$this->_smarty->fetch_string(FILE_UTIL::get($service_template)));

				$this->success();

			} catch(Exception $e) {
				$this->error($e);
			}
		}
	}
