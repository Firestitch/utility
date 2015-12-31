<?php
	

	class VIEW_APPLICATION_FOOTER extends VIEW {

		function __construct() {
			$this->set_route('application','footer');
			$this->disable_authorization();
		}

		function init() {}
		
	}
