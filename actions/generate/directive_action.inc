<?
	class ACTION_GENERATE_DIRECTIVE extends ACTION_JSON {

		function __construct() {
			parent::__construct()->disable_authorization();
		}

		function process() {

			$directive				= $this->post("directive");
			$directive_template		= DIR_INSTANCE."utility/assets/frontend_directive.inc";
			$index_file				= DIR_INSTANCE."frontend/app/index.html";
			$app_dir				= DIR_INSTANCE."frontend/app/";

			$this->_smarty = new CMODEL_SMARTY();
			$this->_smarty->assign("directive",$directive);

			try {


				$index_html = FILE_UTIL::get($index_file);

				if(!$index_html)
					throw new Exception("Failed to locate index.html");

				if(stripos($index_html,"<!-- enddirectives -->")===false)
					throw new Exception("Failed to locate <!-- enddirectives --> index.html");

				$js_include = '<script src="scripts/directives/'.strtolower($directive).'.js"></script>';

				if(stripos($index_html,$js_include)===false) {
					$index_html = str_replace("<!-- enddirectives -->",$js_include."\n\t<!-- enddirectives -->",$index_html);
					FILE_UTIL::put($index_file,$index_html);
				}

				FILE_UTIL::put($app_dir."scripts/directives/".strtolower($directive).".js",$this->_smarty->fetch_string(FILE_UTIL::get($directive_template)));

				$this->success();

			} catch(Exception $e) {
				$this->error($e);
			}
		}
	}
