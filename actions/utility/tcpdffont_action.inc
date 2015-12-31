<?
	class ACTION_UTILITY_TCPDFFONT extends ACTION {
	
		function __construct() {
			$this->set_roles(array(SECURITY_MANAGER::ROLE_PUBLIC_USER));
		}
		
		function process() {
			
			$registry = REGISTRY::get_instance();
			$form = $registry->get_request_parm("form");

			$upload_util = new UPLOAD_UTIL("file");

			if($upload_util->is_file_upload_exist()) {

				$temp_dir = MODEL_PATH::get_temporary_directory();
				FILE_UTIL::mkdir($temp_dir);

				$upload_util->set_store_path($temp_dir);
				
				$filename = preg_replace("/[\s]/","",strtolower($upload_util->get_file_name()));
				
				$upload_util->set_store_filename($filename);
				$upload_util->set_valid_extensions(array("ttf","otf"));
				$has_success = $upload_util->upload();

				if($has_success) {

					$util_dir = MODEL_PATH::get_framework_packages_directory()."tcpdf/fonts/utils/";

					$cmd = $util_dir."ttf2ufm -a -F \"".$upload_util->get_store_file()."\"";

					$output = array();
					$has_success = SERVER_UTIL::exec($cmd,$output,true);

					if($has_success) {
						
						$file_names = FILE_UTIL::get_directory_listing($temp_dir);

						$metric_files = array();
						foreach($file_names as $file_name) {
							$ext = FILE_UTIL::get_file_extension($file_name);
							$metric_files[$ext] = $file_name;
						}
						
						$configs = array();
						$configs[] = array("ttf","ufm");
						$configs[] = array("otf","ufm");
						$configs[] = array("ttf","afm");
						$configs[] = array("otf","afm");
						$configs[] = array("pfb","afm");

						$has_success = false;

						$metric_file = $font_file = null;

						foreach($configs as $config) {

							$tmp_font_file 		= get_value($metric_files,$config[0]);
							$tmp_metric_file 	= get_value($metric_files,$config[1]);

							if($tmp_font_file && $tmp_metric_file) {
								$metric_file 	= $tmp_metric_file;
								$font_file 	= $tmp_font_file;			
							}
						}

						if($metric_file && $font_file) {

							$cmd = 'cd '.$temp_dir.' && php -q '.$util_dir.'makefont.php "'.$temp_dir.$font_file.'" "'.$temp_dir.$metric_file.'"';

							$has_success = SERVER_UTIL::exec($cmd,$output,true);								

							if($has_success) {

								$zip_file = FILE_UTIL::remove_file_extension($filename).".zip";

								$zip_util = new ZIP_UTIL($zip_file);
								$zip_util->add_directory_contents($temp_dir);
								$zip_util->download(basename($zip_file),false);	
							} else 
								APPLICATION::add_error_messages($output);					
						} else
							APPLICATION::add_error("Failed to find the metric file combination");

					} else
						APPLICATION::add_error_messages($output);

				} else
					APPLICATION::add_error($upload_util->get_error_message());

				FILE_UTIL::rmdir($temp_dir);
			} else
				APPLICATION::add_error("No font file was supplied");
			
			$view = APPLICATION::get_view_instance("utility","tcpdffont");
			$this->set_view($view);
		}
	}