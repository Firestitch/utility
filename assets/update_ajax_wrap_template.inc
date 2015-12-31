<? if(${$lower_model}) { ?>

<h1 class="fl">{$pretty_model|capitalize}</h1>
<? if(${$lower_model}->get_{$lower_model}_id()) { ?>
	<h2 class="fl pl10">{if $has_name}<?=${$lower_model}->get_name()?>{else}<?=${$lower_model}->get_{$primary_key}()?>{/if}</h2>
<? } ?>

<div class="fr"><a href="/{$lower_controller}/{$lower_task_plural}/" class="{$small_button_class}">{$plural_pretty_model|capitalize}</a></div>
<div class="cb"></div>

<div id="{$lower_model}-tabs" class="dn">

	<ul>
		<li><a href="/{$lower_controller}/{$lower_task}overview/{$id}:<?=${$lower_model}->get_{$primary_key}()?>"><span>Overview</span></a></li>
	</ul>
	
</div>

<script>
	$(function() {
		$("#{$lower_model}-tabs").tabs({activate: function(e,ui) {
									FF.cookie.set("{$lower_task}-tabs",ui.newTab.index());
								}}).show().tabs("option","active",((idx=FF.request.get("tab")) ? idx : parseInt(FF.cookie.get("{$lower_task}-tabs"))));
	});
</script>

<? } ?>