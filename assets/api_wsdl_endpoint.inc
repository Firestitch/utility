									[	"path"=>["{$parent_method}{$method}","{$parent_method}{$method}/{$model_id}"],
										"method"=>"get",
										"parms"=>[
											"order"=>[{foreach $accessible_fields as $index=>$name}{if $name=="name"}"name"=>"By name"{/if}{/foreach}]
										],
										"response"=>["{$model_plural}","{$model}"]
									],
									[	"path"=>"{$parent_method}{$method}/{$model_id}",
										"method"=>"put",
										"parms"=>[{foreach $accessible_fields as $index=>$name}{if $name=="state"}"state"=>CMODEL_{$model_upper}::get_states(){else}"{$name}"{/if}{if $index<(count($accessible_fields)-1)},{/if}{/foreach}],
										"response"=>["{$model}"]
									],
									[	"path"=>"{$parent_method}{$method}",
										"method"=>"post",
										"parms"=>[{foreach $accessible_fields as $index=>$name}{if $name=="state"}"state"=>CMODEL_{$model_upper}::get_states(){else}"{$name}"{/if}{if $index<(count($accessible_fields)-1)},{/if}{/foreach}],
										"response"=>["{$model}"]
									],
									[	"path"=>"{$parent_method}{$method}/{$model_id}",
										"method"=>"delete",
										"response"=>["{$model}"]
									]{if in_array("order",$options)},
									[	"path"=>"{$parent_method}{$method}/order",
										"method"=>"put",
										"param"=>["{$model}_ids"],
										"response"=>[]
									]
{/if}