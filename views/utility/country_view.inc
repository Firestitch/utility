<?
	class VIEW_UTILITY_COUNTRY extends VIEW {
	
		function __construct() {
			parent::__construct("utility","country",SECURITY_MANAGER::ROLE_PUBLIC_USER,"form-country");
		}
		
		function init() {

		}		
	}