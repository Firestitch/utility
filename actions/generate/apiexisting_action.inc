<?
	class ACTION_GENERATE_APIEXISTING extends ACTION_JSON {

		public function __construct() {
			parent::__construct()->disable_authorization();
		}

		public function process() {

			$dir 			= APPLICATION::get_main_application_directory();
			$model 			= $this->post("model");
			$method 		= $this->post("method");
			$model_plural	= $this->post("model-plural");

			if(!$model)
				throw new Exception("Invalid model");

			if(!$model_plural)
				throw new Exception("Plural model");

			$messages = [];
			CMODEL_API_GENERATOR::create(	$dir,
											$this->post("api"),
											$model,
											$model_plural,
											rtrim($this->post("api"),"s"),
											["method"=>$method])->append($messages);

			$this->data("messages",$messages)->success();
		}
	}