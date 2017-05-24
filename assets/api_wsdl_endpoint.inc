									[	"path"=>["{$parent_method}{$method}","{$parent_method}{$method}/{$model_id}"],
										"method"=>"get",
										"parms"=>[
{foreach $loads as $name}											"load_{$name}",
{/foreach}{foreach from=$accessible_fields item=name name=accessible_fields}											"{$name}"{if !$smarty.foreach.accessible_fields.last},
{/if}{/foreach}
										],
										"response"=>["{$model_plural}","{$model}"]
									],
									[	"path"=>"{$parent_method}{$method}/{$model_id}",
										"method"=>"put",
										"parms"=>[
											{foreach from=$accessible_fields item=name name=accessible_fields}{if $name=="state"}"state"=>CMODEL_{$model_upper}::get_states(){else}"{$name}"{/if}{if !$smarty.foreach.accessible_fields.last},
											{/if}{/foreach}
										],
										"response"=>["{$model}"]
									],
									[	"path"=>"{$parent_method}{$method}",
										"method"=>"post",
										"parms"=>[
											{foreach from=$accessible_fields item=name name=accessible_fields}{if $name=="state"}"state"=>CMODEL_{$model_upper}::get_states(){else}"{$name}"{/if}{if !$smarty.foreach.accessible_fields.last},
											{/if}{/foreach}
										],
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