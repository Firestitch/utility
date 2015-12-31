<?
	class ACTION_UTILITY_UPDATE extends ACTION_JSON {
	
		function __construct() {
			parent::__construct(array(SECURITY_MANAGER::ROLE_PUBLIC_USER));
		}
		
		function process() {
			
			$revision = COOKIE_UTIL::get("revision");

			$revision_file = MODEL_PATH::get_application_dir()."version/revision";
	
			/*
			if(SERVER_UTIL::exec("svn info ".MODEL_PATH::get_application_dir(),$output)) {

				SERVER_UTIL::exec("svn update ".$revision_file);

				$output = implode("\n",$output);

				preg_match("/Revision: (\d+)/",$output,$matches);

				$local_revision = get_value($matches,1);
				
				FILE_UTIL::put($revision_file,$local_revision);
			}
			*/
			
			$revision = "";			
		
			try {
				$rss = RSS_READER_UTIL::create()->get("https://firestitch.beanstalkapp.com/utility/activity/atom/2b1ac34d71d9197de4e7db09f92f42441e3c9ff7");

				if($link=get_value($rss,array("items",0,"link"))) {
					if(preg_match("/\d+$/",$link,$matches)) {
						
						$revision = get_value($matches,0);
						COOKIE_UTIL::set("revision",$revision,1);
					}
				}

			} catch(Exception $e) {
				
			}

			$local_revision = FILE_UTIL::get($revision_file);

			$success = $revision && $local_revision;

			$this
				->success($success)
				->data("update",$revision!=$local_revision)
				->data("local",$local_revision)
				->data("repo",$revision);
		}
	}