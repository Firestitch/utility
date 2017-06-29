<?
	class ACTION_GENERATE_VC extends ACTION_JSON {

		function __construct() {
			parent::__construct()->disable_authorization();
		}

		function process() {

			$url1 					= $this->post("form1","url");
			$controller1			= $this->post("form1","controller");
			$view1					= $this->post("form1","view");
			$state1					= $this->post("form1","state");
			$object1				= $this->post("form1","object");
			$view_format1			= $this->post("form1","view_format");
			$interface1				= $this->post("form1","interface");
			$override1				= $this->post("form1","override");
			$params1				= $this->post("form1","params") ? explode(",",$this->post("form1","params")) : [];
			$options1				= (array)$this->post("form1","options");
			$child					= in_array("secondary",$options1) ? $this->post("form2") : [];

			$url2 					= $this->post("form2","url");
			$controller2			= $this->post("form2","controller");
			$view2					= $this->post("form2","view");
			$state2					= $this->post("form2","state");
			$view_format2			= $this->post("form2","view_format");
			$interface2				= $this->post("form2","interface");
			$override2				= $this->post("form2","override");
			$params2				= $this->post("form2","params") ? explode(",",$this->post("form2","params")) : [];
			$options2				= (array)$this->post("form2","options");

			try {

				if(!$controller1)
					throw new Exception("Please supply a controller name");

				if(!$view1)
					throw new Exception("Please supply a view name");

				if(!$state1)
					throw new Exception("Please supply an state name");

				if(!$object1 && $interface1)
					throw new Exception("Please supply an object name");

				$this->create_view_controller(	$url1,
												$state1,
												$params1,
												$controller1,
												$view1,
												$view_format1,
												$interface1,
												$object1,
												$child,
												[],
												$options1,
												$override1);

				if($interface1=="lister" && in_array("secondary",$options1)) {

					if(!$controller2)
						throw new Exception("Please supply a controller name");

					if(!$view2)
						throw new Exception("Please supply a view name");

					if(!$state2)
						throw new Exception("Please supply an state name");

					$this->create_view_controller(	$url2,
													$state2,
													$params2,
													$controller2,
													$view2,
													$view_format2,
													$interface2,
													$object1,
													[],
													(array)$this->post("form1"),
													$options2,
													$override2);
				}
				$this->success();

			} catch(Exception $e) {
				$this->error($e);
			}
		}

		function create_view_controller($url,$state,$params,$controller,$view,$view_format,$interface,$object,$child=[],$parent=[],$options=[],$override=false) {

			$controller_template	= DIR_INSTANCE."utility/assets/frontend_controller.inc";
			$view_template			= DIR_INSTANCE."utility/assets/frontend_view.inc";
			$app_js_file			= DIR_INSTANCE."frontend/app/scripts/app.js";
			$index_file				= DIR_INSTANCE."frontend/app/index.html";
			$state_file				= DIR_INSTANCE."utility/assets/frontend_state.js";
			$app_dir				= DIR_INSTANCE."frontend/app/";

			$state_model_options = [];
			if(in_array("draft",$options))
				$state_model_options["draft"] = true;

			if(preg_match("/\/[^\/]+\/:([a-z0-9_]+)/",$url,$matches)) {

				$id_name = $matches[1];
				if($id_name && $id_name!=="id")
					$state_model_options["id_name"] = $id_name;
			}

			if($state_model_options) {
				foreach($state_model_options as $name=>&$state_model_option) {
					if(is_bool($state_model_option))
						$state_model_option = $state_model_option ? 'true' : 'false';
					else
						$state_model_option = "'".$state_model_option."'";

					$state_model_option = $name.": ".$state_model_option;
				}

				$state_model_options = "{ ".implode(", ",$state_model_options)." }";
			}

			$smart_cmodel = CMODEL_SMARTY::create()
								->assign("controller",$controller)
								->assign("state_model_options",$state_model_options)
								->assign("controller_singular",rtrim($controller,"s"))
								->assign("object",$object)
								->assign("plural_object",LANG_UTIL::plural($object))
								->assign("url",$url)
								->assign("view",$view)
								->assign("state",$state)
								->assign("interface",$interface)
								->assign("view_format",$view_format)
								->assign("options",$options)
								->assign("parent",$parent)
								->assign("child",$child)
								->assign("params",$params)
								->assign("path",str_replace("/","-",$url));

			$controller_file = $app_dir."scripts/controllers/".strtolower($controller).".js";

			if(is_file($controller_file) && !$override)
				throw new Exception("The ".$controller." controller already exists");

			$app_js = FILE_UTIL::get($app_js_file);

			if(!$app_js)
				throw new Exception("Failed to locate app.js");

			if($state) {

				if(stripos($app_js,"/** endstates **/")===false)
					throw new Exception("Failed to locate /** endstates **/ in app.js");

				if(stripos($app_js,".state('".$state."'")===false) {

					$state = $smart_cmodel->fetch_string(FILE_UTIL::get($state_file));

					$state .= "\n\n\t/** endstates **/";

					$app_js = str_replace("/** endstates **/",$state,$app_js);

					FILE_UTIL::put($app_js_file,$app_js);
				}
			}

			$index_html = FILE_UTIL::get($index_file);

			if(!$index_html) {
				throw new Exception("Failed to locate index.html");
			}

			if(stripos($index_html,"<!-- endcontrollers -->")===false)
				throw new Exception("Failed to locate <&#33;-- endcontrollers --> index.html");

			$js_include = '<script src="scripts/controllers/'.strtolower($controller).'.js"></script>';

			if(stripos($index_html,$js_include)===false) {
				$index_html = str_replace("<!-- endcontrollers -->",$js_include."\n\t<!-- endcontrollers -->",$index_html);

				FILE_UTIL::put($index_file,$index_html);
			}

			FILE_UTIL::put($controller_file,$smart_cmodel->fetch_string(FILE_UTIL::get($controller_template)));

			if($view)
				FILE_UTIL::put($app_dir."views/".strtolower($view).".html",$smart_cmodel->fetch_string(FILE_UTIL::get($view_template)));
		}
	}
