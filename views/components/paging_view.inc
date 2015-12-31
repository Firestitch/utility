<?php

	class VIEW_COMPONENTS_PAGING extends VIEW {

		private $_page_index = 0;
		private $_page_count = 0;
		
		function __construct() {
			$this->set_route("components","paging");
			$this->set_roles(array(SECURITY_MANAGER::ROLE_PUBLIC_USER));
		}

		function init() {
			$this->set_var("page_index",$this->_page_index);
			$this->set_var("page_count",$this->_page_count);
			$this->set_var("page_number",$this->_page_index + 1);
		}
		
		function set_page_index($page_index) 	{ $this->_page_index = $page_index; }
		function set_page_count($page_count) 	{ $this->_page_count = $page_count; }
	}