<?
	class ACTION_UTILITY_FBAUTHORIZATION extends ACTION {
	
		function __construct() {
			$this->set_roles(array(SECURITY_MANAGER::ROLE_PUBLIC_USER));
		}
		
		function process() {
			
			$registry 		= REGISTRY::get_instance();
			$code 			= $registry->get_request_parm("code");
			$facebook_app_id 	= $registry->get_request_parm("faid");
			$facebook_secrect 	= $registry->get_request_parm("fs");
			
			if($code) {

				$facebook_client = BASE_MODEL_FACEBOOK::_create_client($facebook_app_id,$facebook_secrect);

				$facebook_authorization_cmodel = new BASE_CMODEL_FACEBOOK_AUTHORIZATION($facebook_client);
				$facebook_authorization_cmodel->debug();

				try {
					$facebook_authorization_cmodel->process_code_from_request();

				} catch (Exception $e) {
					
					APPLICATION::add_error_message($e->getMessage());
				}

			} elseif($facebook_app_id) {
				
				$redirect_uri = SYSTEM_MANAGER::get_system_url()."utility/dofbauthorization/faid:".$facebook_app_id."/fs:".$facebook_secrect."/action:code/";
				
				$url = BASE_MODEL_FACEBOOK::get_authorize_url($facebook_app_id,$redirect_uri,array("email"));
				
				HTTP_UTIL::redirect($url);
			}
			
			$view = APPLICATION::get_view_instance("utility","fbauthorization");
			$view->set_facebook_app_id($facebook_app_id);
			$view->set_facebook_secrect($facebook_secrect);
			$this->set_view($view);
		}
	}