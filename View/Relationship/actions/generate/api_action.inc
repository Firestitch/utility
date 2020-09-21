<?
	class ACTION_GENERATE_API extends ACTION_JSON {

		public function __construct() {
			parent::__construct()->disable_authorization();
		}

		public function process() {

			$dir 			= APPLICATION::get_main_application_directory();
			$model 			= $this->post("model");
			$api 			= $this->post("api");
			$model_plural	= $this->post("model-plural");
			$options		= (array)$this->post("options");
			$methods		= (array)$this->post("methods");
			$method 		= $this->post("method");

			if(!$model)
				throw new Exception("Invalid model");

			if(!$model_plural)
				throw new Exception("Plural model");

			$messages = [];

			if($api) {
				$options["method"] = $method;
				CMODEL_API_GENERATOR::create(	$dir,
												$api,
												$model,
												$model_plural,
												$methods,
												rtrim($api,"s"),
												$options)
					->append($messages);
			} else {
				CMODEL_API_GENERATOR::create(	$dir,
												$model_plural,
												$model,
												$model_plural,
												$methods,
												"",
												$options)
					->generate(in_array("override",$options),$messages);
			}

			$this->data("messages",$messages)->success();
		}
	}