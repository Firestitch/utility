<?php
	class SOU {
		private $_user_id 		= null;
		private $_username		= null;
		private $_roles			= array();
		private $_settings		= array();
		
	
		function get_user_id() 			{ return $this->_user_id; }
		function get_roles() 			{ return $this->_roles; }
		function get_username() 		{ return $this->_username; }
		function get_setting($key) 		{ return get_value($this->_settings,$key); }
		
		function set_user_id($value) 		{ $this->_user_id = $value; }
		function set_roles($value) 		{ $this->_roles = $value; }
		function set_username($value) 		{ $this->_username = $value; }
		function set_setting($key,$value) 	{ $this->_settings[$key] = $value; }
		
		function remove_setting($key) 		{ unset($this->_settings[$key]); }
		function setting_exists($key) 		{ return array_key_exists($key,$this->_settings); }
		function is_logged_in() 		{ return $this->_user_id!=null; }
	}

	

	class USER_SESSION extends SESSION {

		protected function get_default_session_object() { return new SOU(); }

		public static function destroy() { SESSION_MANAGER::destroy(__CLASS__); }

		public static function has_session() { return SESSION_MANAGER::has_session(__CLASS__); }

		static function get_instance() {
			$session_obj = new USER_SESSION();
			return $session_obj->get_session_object_by_ref();
		}
	}	