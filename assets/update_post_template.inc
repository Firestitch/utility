<? if(${$lower_model}) { ?>
{if !$is_interface_popup}
	<div class="fr"><a href="/{$lower_controller}/{$lower_task_plural}/{if $relation_field_abr}{$relation_field_abr}:<?=${$lower_model}->get_{$relation_field}()?>{/if}" class="{$small_button_class}">{$plural_pretty_model|capitalize}</a></div>
{/if}
{if $is_interface_popup}

<h3>{$pretty_model|capitalize}
<? if(${$lower_model}->get_{$primary_key}()) { ?>
	<span class="pl5 fss">{if $has_name}<?=${$lower_model}->get_name()?>{else}<?=${$lower_model}->get_{$primary_key}()?>{/if}</span>
<? } ?>
</h3>

{else}

<h1>{$pretty_model|capitalize}
<? if(${$lower_model}->get_{$primary_key}()) { ?>
	<span class="pl5 fss">{if $has_name}<?=${$lower_model}->get_name()?>{else}<?=${$lower_model}->get_{$primary_key}()?>{/if}</span>
<? } ?>
</h1>

{/if}

<div class="cb"></div>


<div id="{$lower_model}-tabs" class="dn">

	<ul>
		<li><a href="#overview"><span>Overview</span></a></li>
	</ul>
	
	<div id="overview">

{if $is_method_post}
	<form action="/{$lower_controller}/do{$lower_task}" method="post" id="{$hyphen_model}-form">	
{/if}

{if $is_method_ajax}
		<form action="javascript:;" method="post" id="{$hyphen_model}-form">
{/if}
			<?
				FORM_UTIL::create()
{foreach $dependent_objects as $object=>$field}					->dropdown("form[{$object}_id]","{$object|pretty}",${$object}_list,${$lower_model}->get_{$object}_id())
{/foreach}
{foreach $columns as $k=>$v}{if $k=="state"}					->dropdown("form[{$k}]","{$v->get_value()}",DBQ_{$upper_model}::get_state_list(),${$lower_model}->get_{$k}())
{else}{if $v->get_data_type()=="char"}					->dropdown("form[{$k}]","{$v->get_value()}",DBQ_{$upper_model}::get_{$k}_list(),${$lower_model}->get_{$k}())
{else}					->input("form[{$k}]","{$v->get_value()}",${$lower_model}->get_{$k}())
{/if}{/if}{/foreach}					->text("", HTML_UTIL::link("javascript:;","Save",array("class"=>"btn","id"=>"{$hyphen_model}-save")))
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