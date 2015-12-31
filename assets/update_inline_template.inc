<? if(${$lower_model}) { ?>

<div class="pt15"></div>

<h3>{$pretty_model|capitalize}</h3>

<?
	$html_form = new FORM_UTIL();
{foreach $dependent_objects as $object=>$field}	$html_form->add_dropdown("form[{$object}_id]","{$object|pretty}",${$object}_list,${$lower_model}->get_{$object}_id());
{/foreach}
{foreach $columns as $k=>$v}{if $k=="state"}	$html_form->add_dropdown("form[{$k}]","{$v->get_value()}",DBQ_{$upper_model}::get_state_list(),${$lower_model}->get_{$k}());
{else}{if $v->get_data_type()=="char"}	$html_form->add_dropdown("form[{$k}]","{$v->get_value()}",DBQ_{$upper_model}::get_{$k}_list(),${$lower_model}->get_{$k}());
{else}	$html_form->add_input("form[{$k}]","{$v->get_value()}",${$lower_model}->get_{$k}());
{/if}{/if}{/foreach}	$html_form->add_static("", HTML_UTIL::get_button("{$hyphen_model}-save","Save")." ".HTML_UTIL::get_button("{$hyphen_model}-cancel","Cancel",array("type"=>"button")));
	$html_form->render();
?>

<?=HTML_UTIL::get_hidden("{$id}",${$lower_model}->get_{$primary_key}())?>
{if $relation_field_abr}
<?=HTML_UTIL::get_hidden("{$relation_field_abr}",${$lower_model}->get_{$relation_field}())?>
{/if}

<? } ?>