<?
	
	class SECURITY_MANAGER {
		
		const ROLE_PUBLIC_USER				= "PU";
		const ROLE_BACKEND_USER				= "BU";
		const ROLE_FRONTEND_USER			= "FU";
		
		static function get_role_list() {
			return array(self::ROLE_PUBLIC_USER=>				"Public user",
				     self::ROLE_BACKEND_USER=>				"Backend user",
				     self::ROLE_FRONTEND_USER=>				"Frontend user");
		}
	}