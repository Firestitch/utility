<?
	class ACTION_{$upper_controller}_{$upper_task} extends ACTION_JSON {
	
		public function __construct() {
			parent::__construct(array({$security_roles}));
		}
		
		public function process() {
			
		}
	}