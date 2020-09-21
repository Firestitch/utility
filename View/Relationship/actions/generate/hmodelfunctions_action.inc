<?
	class ACTION_GENERATE_HMODELFUNCTIONS extends ACTION_JSON {

		public function __construct() {
			parent::__construct()->disable_authorization();
		}

		public function process() {

			$file = APPLICATION::get_main_application_directory()."models/handler/".$this->request("model")."_handler_model.inc";

			$file = FILE_UTIL::get($file);

			preg_match_all("/function load_([^\s\(]+)/",$file,$matches);

			$functions = [];
			foreach(value($matches,0) as $index=>$match) {
				$functions[] = value($matches,[1,$index]);
			}

			$this->data("functions",$functions)->success();
		}
	}