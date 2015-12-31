<?php
	
	class VIEW_INDEX_INDEX extends VIEW {

		function __construct() {
			$this->set_route("index","index");
			$this->disable_authorization();
		}

		function init() {
			
		}
		
	}
