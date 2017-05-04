<?
	class ACTION_GENERATE_SERVICEEXISTING extends ACTION_JSON {

		function __construct() {
			parent::__construct()->disable_authorization();
		}

		function process() {

			$service				= $this->post("form","service");
			$model					= $this->post("form","model");
			$plural_model			= $this->post("form","model-plural");
			$service_template		= DIR_INSTANCE."utility/assets/frontend_service_existing.inc";
			$index_file				= DIR_INSTANCE."frontend/app/index.html";
			$app_dir				= DIR_INSTANCE."frontend/app/";
			$method 				= str_replace($service."_","",$plural_model);

			$this->_smarty = new CMODEL_SMARTY();
			$this->_smarty->assign("service",$service);
			$this->_smarty->assign("model",$model);
			$this->_smarty->assign("method",$method);
			$this->_smarty->assign("plural_model",$plural_model);
			$this->_smarty->assign("api_singular",rtrim($service,"s"));

			try {

				$file = $app_dir."scripts/services/".strtolower($service).".js";

				if(!is_file($file))
					throw new Exception("Failed to located service");

				$code = FILE_UTIL::get($file);



				$service = [	"\t\t\t".$method."Get:".$method."Get",
								"\t\t\t".$method."Gets:".$method."Gets",
								"\t\t\t".$method."Post:".$method."Post",
								"\t\t\t".$method."Put:".$method."Put",
								"\t\t\t".$method."Delete:".$method."Remove"
							];

				$code = preg_replace("/(var\s+service\s+=\s+{[^;]+?)\s*,?\s*}\s*;/si","$1,\n".implode(",\n",$service)."\n\t\t};",$code);


				$crud = $this->_smarty->fetch_string(FILE_UTIL::get($service_template));

				$code = preg_replace("/(\}\);\s*}\)\(\);)/",$crud."\n\t$1",$code,1);

				FILE_UTIL::put($file,$code);

				$this->success();

			} catch(Exception $e) {
				$this->error($e);
			}
		}
	}
