<?
	class ACTION_GENERATE_VC extends ACTION_JSON {

		function __construct() {
			parent::__construct()->disable_authorization();
		}

		function process() {

			$url 					= trim($this->post("form","url"),"/");
			$controller				= $this->post("form","controller");
			$view					= $this->post("form","view");
			$state					= $this->post("form","state");
			$controller_template	= DIR_INSTANCE."utility/assets/frontend_controller.inc";
			$view_template			= DIR_INSTANCE."utility/assets/frontend_view.inc";
			$app_js_file			= DIR_INSTANCE."frontend/app/scripts/app.js";
			$index_file				= DIR_INSTANCE."frontend/app/index.html";
			$app_dir				= DIR_INSTANCE."frontend/app/";

			$this->_smarty = new CMODEL_SMARTY();
			$this->_smarty->assign("controller",$controller);

			try {

				$app_js = FILE_UTIL::get($app_js_file);

				if(!$app_js)
					throw new Exception("Failed to locate app.js");

				if(stripos($app_js,"/** endstates **/")===false)
					throw new Exception("Failed to locate /** endstates **/ in app.js");

				if(stripos($app_js,".state('".$state."'")===false) {

					$state = 	".state('" . $state . "', {\n" .
								"		url: '/" . $url . "/:param',\n" .
								"		templateUrl: 'views/" . $view . ".html',\n" .
								"		controller: '" . $controller . "Ctrl',\n" .
								"		params: {\n" .
								"			param: { squash: true, value: null }\n" .
								"		},\n" .
								"		resolve: {\n" .
								"			param: function(\$stateParams) {\n" .
								"				return \$stateParams.param;\n" .
								"			}\n" .
								"		}\n" .
								"\t})\n\n\t/** endstates **/";

					$app_js = str_replace("/** endstates **/",$state,$app_js);

					FILE_UTIL::put($app_js_file,$app_js);
				}


				$index_html = FILE_UTIL::get($index_file);

				if(!$index_html)
					throw new Exception("Failed to locate index.html");

				if(stripos($index_html,"<!-- endcontrollers -->")===false)
					throw new Exception("Failed to locate <!-- endcontrollers --> index.html");

				$js_include = '<script src="scripts/controllers/'.strtolower($controller).'.js"></script>';

				if(stripos($index_html,$js_include)===false) {
					$index_html = str_replace("<!-- endcontrollers -->",$js_include."\n\t<!-- endcontrollers -->",$index_html);

					FILE_UTIL::put($index_file,$index_html);
				}

				FILE_UTIL::put($app_dir."scripts/controllers/".strtolower($controller).".js",$this->_smarty->fetch_string(FILE_UTIL::get($controller_template)));

				FILE_UTIL::put($app_dir."views/".strtolower($view).".html",$this->_smarty->fetch_string(FILE_UTIL::get($view_template)));

				$this->success();

			} catch(Exception $e) {
				$this->error($e);
			}
		}
	}
