<? if(${$lower_model}) { ?>
{if !$is_interface_popup}
{if $relation_field_abr}
<div class="fr"><a href="/{$lower_controller}/{$relation_field_controller}/{$relation_field_abr}:<?=${$lower_model}->get_{$relation_field}()?>" class="{$small_button_class} btn-back">{$pretty_relation_field|capitalize}</a></div>
{else}
<div class="fr"><a href="/{$lower_controller}/{$lower_task_plural}/" class="{$small_button_class} btn-back">{$lower_task|capitalize|pluralize}</a></div>
{/if}{/if}


<h1>{$pretty_model|capitalize}
<? if(${$lower_model}->get_{$primary_key}()) { ?>
	<span class="pl5 fss">{if $has_name}<?=${$lower_model}->get_name()?>{else}<?=${$lower_model}->get_{$primary_key}()?>{/if}</span>
<? } ?>
</h1>

<div class="cb"></div>

<div id="{$lower_model}-tabs" class="dn">

	<ul>
		<li><a href="#overview"><span>Overview</span></a></li>
	</ul>
	
	<div id="overview">

		<form action="javascript:;" method="post" id="{$hyphen_model}-form">	
			<?
				FORM_UTIL::create()
{if $dependent_objects}/*	
{foreach $dependent_objects as $object=>$field}					->dropdown("form[{$object}_id]","{$object|pretty}",${$object}_list,${$lower_model}->get_{$object}_id())
{/foreach}*/{/if}
{foreach $columns as $k=>$v}{if $k=="state"}					->dropdown("form[{$k}]","{$v->label}",{if $cmodel_state}CMODEL_{$upper_model}::get_states(){else}DBQ_{$upper_model}::get_state_list(){/if},${$lower_model}->get_{$k}())
{else}{if $v->get_data_type()=="char"}					//->dropdown("form[{$k}]","{$v->label}",CMODEL_{$upper_model}::get_{$k}s(),${$lower_model}->get_{$k}())
{else}					->input("form[{$k}]","{$v->label}",${$lower_model}->get_{$k}())
{/if}{/if}{/foreach}					->text("", HTML_UTIL::button("{$hyphen_model}-save","Save",array("id"=>"{$hyphen_model}-save","class"=>"btn btn-primary")))
					->render();
			?>

			<?=HTML_UTIL::hidden("{$id}",${$lower_model}->get_{$primary_key}())?>
{if $relation_field_abr}
			<?=HTML_UTIL::hidden("{$relation_field_abr}",${$lower_model}->get_{$relation_field}())?>
{/if}
		</form>

	</div>
</div>

<script>
	$(function() {
		
		$("#{$lower_model}-tabs").tabs({ activate: function(e,ui) {
									FF.cookie.set("{$lower_task}-tabs",ui.newTab.index());
								}}).show().tabs("option","active",((idx=FF.request.get("tab")) ? idx : parseInt(FF.cookie.get("{$lower_task}-tabs"))));

		$("#{$hyphen_model}-save").go("/{$lower_controller}/do{$lower_task}",{ data: "#{$hyphen_model}-form", message: "Successfully saved the {$pretty_model}" });
		
	});	
</script>

<? } ?>