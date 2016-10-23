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
			$object					= $this->post("form","object");
			$view_format			= $this->post("form","view_format");
			$lister					= $this->post("form","lister");

			try {

				if(!$url)
					throw new Exception("Please supply a URL");

				if(!$controller)
					throw new Exception("Please supply a controller name");

				if(!$view)
					throw new Exception("Please supply a view name");

				if(!$state)
					throw new Exception("Please supply an state name");

				if(!$object)
					throw new Exception("Please supply an object name");

				$this->create_view_controller(	$url,
												$state,
												$controller,
												$view,
												$view_format,
												$object,
												$lister,
												false);

				if($lister=="page" || $lister=="modal" || $lister=="drawer")
					$this->create_view_controller(	rtrim($url,"s"),
													rtrim($state,"s"),
													rtrim($controller,"s"),
													rtrim($view,"s"),
													$lister, //view_format which is carried over from parent lister option
													$object,
													false,
													true); //enable edit template features
				$this->success();

			} catch(Exception $e) {
				$this->error($e);
			}
		}

		function create_view_controller($url,$state,$controller,$view,$view_format,$object,$lister,$edit) {

			$controller_template	= DIR_INSTANCE."utility/assets/frontend_controller.inc";
			$view_template			= DIR_INSTANCE."utility/assets/frontend_view.inc";
			$app_js_file			= DIR_INSTANCE."frontend/app/scripts/app.js";
			$index_file				= DIR_INSTANCE."frontend/app/index.html";
			$state_file				= DIR_INSTANCE."utility/assets/frontend_state.js";
			$app_dir				= DIR_INSTANCE."frontend/app/";

			$smart_cmodel = new CMODEL_SMARTY();
			$smart_cmodel->assign("controller",$controller);
			$smart_cmodel->assign("controller_singular",rtrim($controller,"s"));
			$smart_cmodel->assign("object",$object);
			$smart_cmodel->assign("url","/".$url);
			$smart_cmodel->assign("view",$view);
			$smart_cmodel->assign("state",$state);
			$smart_cmodel->assign("lister",$lister);
			$smart_cmodel->assign("edit",$edit);
			$smart_cmodel->assign("view_format",$view_format);
			$smart_cmodel->assign("path",str_replace("/","-",$url));

			$app_js = FILE_UTIL::get($app_js_file);

			if(!$app_js)
				throw new Exception("Failed to locate app.js");

			if(stripos($app_js,"/** endstates **/")===false)
				throw new Exception("Failed to locate /** endstates **/ in app.js");

			if(stripos($app_js,".state('".$state."'")===false) {

				$state = $smart_cmodel->fetch_string(FILE_UTIL::get($state_file));

				$state .= "\n\n\t/** endstates **/";

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

			FILE_UTIL::put($app_dir."scripts/controllers/".strtolower($controller).".js",$smart_cmodel->fetch_string(FILE_UTIL::get($controller_template)));

			FILE_UTIL::put($app_dir."views/".strtolower($view).".html",$smart_cmodel->fetch_string(FILE_UTIL::get($view_template)));

		}
	}
