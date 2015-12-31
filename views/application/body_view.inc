<?
	class VIEW_APPLICATION_BODY extends BASE_VIEW_APPLICATION_BODY {
		
		function __construct() {
			parent::__construct();
			
			$this->set_route("application","body");
			
			$this->set_view("header",APPLICATION::get_view_instance("application","header"));
			$this->set_view("footer",APPLICATION::get_view_instance("application","footer"));
			
			$this->disable_authorization();
		}

		function init() {
			parent::init();
				
			self::add_web_assets($this->get_web_asset_manager());

			$this->set_var("web_asset_manager",$this->get_web_asset_manager());
		}

		static function add_web_assets($web_asset_manager_cmodel) {
			$web_asset_manager_cmodel
				->add_js_url("//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js")
				->add_js_url("//ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js")
				->add_js_url("//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js")
				->add_js_lib("jquery/plugins/colorbox/colorbox/jquery.colorbox-min.js")
				->add_js_lib("common.js")
				->add_js_app("global.js")
				->add_js_lib("jquery/plugins/colorbox/example1/colorbox.css")
				->add_css_url("//netdna.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css")
				->add_css_url("//fonts.googleapis.com/css?family=Open+Sans")
				->add_css_lib("base.css")
				->add_css_app("styles.css");
		}	
	}

