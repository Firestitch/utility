<?
	class VIEW_API_{$model_plural_upper_trim} extends VIEW_API_BASE {

{{$endpoint}}

{{$wsdl_function}}

		public function wsdl() {
			return [ "model"=>"{$model}", "name"=>"{$model_name}" ];
		}
	}