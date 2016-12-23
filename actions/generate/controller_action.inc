<?
	class ACTION_GENERATE_CONTROLLER extends ACTION_JSON {

		function __construct() {
			parent::__construct()->disable_authorization();
		}

		function process() {

			$url 					= $this->post("url");
			$controller				= $this->post("controller");
			$view					= $this->post("view");
			$state					= $this->post("state");
			$object					= $this->post("object");
			$view_format			= $this->post("view_format");
			$interface				= $this->post("interface");
			$options				= (array)$this->post("options");

			ACTION_GENERATE_VC::create_view_controller($url,$state,$controller,$view,$view_format,$interface,$object);

			$this->success();
		}
	}
