									[	"path"=>["{$model_plural_trim}","{$model_plural_trim}/{$model_id}"],
										"description"=>"Retrieve {$model_plural_proper} or a {$model_proper}",
										"method"=>"get",
										"parms"=>[
											"order"=>[{foreach $accessible_fields as $index=>$name}{if $name=="name"}"name"=>"By name"{/if}{/foreach}]
										],
										"response"=>["{$model_plural}","{$model}"]
									],
									[	"path"=>"{$model_plural_trim}/{$model_id}",
										"description"=>"Update a {$model_proper}",
										"method"=>"put",
										"parms"=>[{foreach $accessible_fields as $index=>$name}{if $name=="state"}"state"=>CMODEL_{$model_upper}::get_states(){else}"{$name}"{/if}{if $index<(count($accessible_fields)-1)},{/if}{/foreach}],
										"response"=>["{$model}"]
									],
									[	"path"=>"{$model_plural_trim}",
										"description"=>"Create a {$model_proper}",
										"method"=>"post",
										"parms"=>[{foreach $accessible_fields as $index=>$name}{if $name=="state"}"state"=>CMODEL_{$model_upper}::get_states(){else}"{$name}"{/if}{if $index<(count($accessible_fields)-1)},{/if}{/foreach}],
										"response"=>["{$model}"]
									],
									[	"path"=>"{$model_plural_trim}/{$model_id}",
										"description"=>"Delete a {$model_proper}",
										"method"=>"delete",
										"response"=>["{$model}"]
									]