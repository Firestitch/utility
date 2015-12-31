<?	
	class SYSTEM_MANAGER extends BASE_SYSTEM_MANAGER {
		
		static function get_index_view() 				{ return APPLICATION::get_view_instance("generate","db"); }
		static function get_restricted_view() 			{ return APPLICATION::get_view_instance("application","restricted"); }
		static function get_not_found_view() 			{ return APPLICATION::get_view_instance("application","notfound"); }
		static function get_login_view()				{ return APPLICATION::get_view_instance("user","login"); }	
		static function get_allowable_contexts()		{ return array(""); }
		static function get_session_timeout_minutes()	{ return 90; }
		static function has_access($object_roles) 		{ return true; }
		static function requires_session()				{ return false; }
		static function is_error_debug_messages()		{ return false; }
	
		static function initialize() {
			
			parent::initialize();
			
			HTML_UTIL::set_defaults(array(	"input"=>array("class"=>"form-control"),
											"button"=>array("class"=>"btn btn-default"),
											"textarea"=>array("class"=>"form-control"),
											"dropdown"=>array("class"=>"form-control")));

			HTML_TABLE_UTIL::set_defaults("table table-striped table-bordered" ,"","");			

			if(APPLICATION::get_instance()->is_web()) 
				REGISTRY::get_instance()
					->set_posts(XSS_UTIL::decode(REGISTRY::get_instance()->posts()))
					->set_gets(XSS_UTIL::decode(REGISTRY::get_instance()->gets()))
					->set_requests(XSS_UTIL::decode(REGISTRY::get_instance()->requests()));
		}
	}