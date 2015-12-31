<?
	class ACTION_GENERATE_COUNTRY extends ACTION_JSON {

		function __construct() {
			parent::__construct(SECURITY_MANAGER::ROLE_PUBLIC_USER);
		}
		
		function process() {
			
			$action = $this->post("action");

			if($action=="generate") {

				$country_options = (array)$this->post("country");
				$region_options = (array)$this->post("regions");

				$region_options["lat"] 			= (int)value($region_options,"lat");
				$region_options["lng"] 			= (int)value($region_options,"lng");
				$region_options["value_keys"] 	= (int)value($region_options,"value_keys");
				$region_options["keys"] 		= (int)value($region_options,"keys");
				
				$this->data("generated",MODEL_COUNTRY::json_countries($country_options,$region_options));
			}

			$this->success();
		}
	}