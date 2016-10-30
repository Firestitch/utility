<?
	class VIEW_API_{$model_plural_upper_trim} extends VIEW_API_BASE {

{{$endpoint}}

		public function wsdl() {
			return ["model"=>"{$model}",
					"name"=>"{$model_name}",
					"endpoints"=>[
{{$wsdl_endpoint}}
								]
					];
		}
	}