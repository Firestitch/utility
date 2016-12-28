<?
	class ACTION_GENERATE_API extends ACTION_JSON {

		public function __construct() {
			parent::__construct()->disable_authorization();
		}

		public function process() {

			$dir 			= APPLICATION::get_main_application_directory();
			$model 			= $this->post("model");
			$model_plural	= $this->post("model-plural");
			$options		= (array)$this->post("options");

			if(!$model)
				throw new Exception("Invalid model");

			if(!$model_plural)
				throw new Exception("Plural model");

			$messages = array();
			CMODEL_API_GENERATOR::create($dir,$model_plural,$model,$model_plural,"",$options)
				->generate(in_array("override",$options),$messages);

			$this->data("messages",$messages)->success();
		}
	}