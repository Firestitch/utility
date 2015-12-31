<?
	class MODEL_MODEL extends MODEL {
	
		function get_complex_model_file($basename,$app_dir) {
			return $app_dir."models/complex/".strtolower($basename)."_complex_model.inc";		
		}
		
		function get_handler_model_file($basename,$app_dir) {
			return $app_dir."models/handler/".strtolower($basename)."_handler_model.inc";		
		}
	}