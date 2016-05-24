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

			$local_revision = FILE_UTIL::get($revision_file);

			$success = $revision && $local_revision;

			$this
				->success($success)
				->data("update",$revision!=$local_revision)
				->data("local",$local_revision)
				->data("repo",$revision);
		}
	}