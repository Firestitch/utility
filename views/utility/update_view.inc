<?
	class VIEW_UTILITY_UPDATE extends VIEW {
	
		function __construct() {
			$this->set_route("utility","update");
			$this->set_roles(array(SECURITY_MANAGER::ROLE_PUBLIC_USER));
		}
		
		function init() {

			$revision = COOKIE_UTIL::get("revision");

			if(!$revision) {

				try {
					$rss = RSS_READER_UTIL::create()->get("https://firestitch.beanstalkapp.com/utility/activity/atom/2b1ac34d71d9197de4e7db09f92f42441e3c9ff7");

					if($link=get_value($rss,array("items",0,"link"))) {
						if(preg_match("/\d+$/",$link,$matches)) {
							
							$revision = get_value($matches,0);
							COOKIE_UTIL::set("revision",$revision);
						}
					}

				} catch(Exception $e) {}
			}				

			
			SERVER_UTIL::exec("svn info ".MODEL_PATH::get_application_dir(),$output);

			$output = implode("\n",$output);

			preg_match("/Revision: (\d+)/",$output,$matches);

			$current_revision = get_value($matches,1);
			
			if($revision!=$current_revision) {
				p("ASD");
			}
		}		
	}