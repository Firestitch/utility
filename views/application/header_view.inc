<?
	class VIEW_APPLICATION_HEADER extends VIEW {

		function __construct(){
			$this->set_route("application","header");
			$this->disable_authorization();
		}

		function init() {
			
			$dirs = FILE_UTIL::get_directory_listing(DIR_INSTANCE);
			
			$applications = array();
			
			foreach($dirs as $dir) 
				if(is_file(DIR_INSTANCE.$dir."/system/managers/system_manager.inc"))
					$applications[$dir] = $dir;

			$applications["framework"] = "framework";
					
			$active_object	= $this->get("active_object");
			$active_table 	= $this->get("active_table");
						
			$this->set_var("applications",$applications);
			$this->set_var("active_object",$active_object);
			$this->set_var("active_table",$active_table);
			$this->set_var("application",$this->request("application"));
		}

		function post_init() {
			

		}
		
	}
