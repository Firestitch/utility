{if $is_view_format_inline || $is_view_format_popup}
<?=HTML_UTIL::link("javascript:;","Add {$pretty_model|capitalize}",["class"=>"{$small_button_class} btn-primary btn-add fr {$hyphen_model}-update"])?>
{else}
<?=HTML_UTIL::link("/{$lower_controller}/{$lower_task}/{if $relation_field}{$relation_field_abr}:".${$relation_field}."/{/if}","Add {$pretty_model|capitalize}",["class"=>"{$small_button_class} btn-primary btn-add fr"])?>
{/if}

{if !$is_list_body_blank}
<h1>{$pretty_plural_model|capitalize}</h1>
{/if}
<form id="{$hyphen_model}-form" action="javascript:;">
{if $is_search_form}
<div class="search-form">
	<?=HTML_UTIL::input("search[keyword]","",["id"=>"{$hyphen_model}-search-keyword","placeholder"=>"Search"])?>
	{if $has_state}<?=HTML_UTIL::dropdown("search[state]",[""=>"All States"] + {if $cmodel_state}CMODEL_{$upper_model}::get_states(){else}DBQ_{$upper_model}::get_state_list(){/if},{if $cmodel_state}CMODEL_{$upper_model}::STATE_ACTIVE{else}DBQ_{$upper_model}::STATE_ACTIVE{/if},["class"=>"{$hyphen_model}-search-interface"])?>{/if}
</div>
{/if}
{if $relation_field}<?=HTML_UTIL::hidden("{$relation_field_abr}",${$relation_field})?>{/if}
</form>
<div class="cb"></div>

<div id="{$hyphen_model}-list"><?$this->show_view("{$lower_models}")?></div>

<script>

$(function() {

	$("#{$hyphen_model}-list").bind("load",function() {
		$(this).load("/{$lower_controller}/{$lower_task}list/",$("#{$hyphen_model}-form").serializeArray(),function() { $(this).trigger("bind") });
	}).bind("bind",function() {

{if is_view_format_popup}		$(".{$hyphen_model}-update").off().on("click",function() {
			FF.popup.show("/{$lower_controller}/{$lower_task}/{if $relation_field}{$relation_field_abr}:<?=${$relation_field}?>/{/if}" + ($(this).data("{$id}") ? "{$id}:" + $(this).data("{$id}") + "/" : ""),"80%","80%", { onClosed: function() { $("#{$hyphen_model}-list").trigger("load") } });
		});
{/if}
{if $has_priority}
		$("#{$hyphen_model}-table tbody").sortable({
			helper: function(e, ui) {
				ui.children().each(function() {
					$(this).width($(this).width());
				});
				return ui;
			},
			handle: ".order",
			axis: "y",
			stop: function(event, ui) {

				var data = $(this).sortable("serialize");

				_table = $(this);

				$.post("/{$lower_controller}/do{$lower_task}order/{if $relation_field}{$relation_field_abr}:<?=${$relation_field}?>/{/if}",data,function(response) {
					FF.msg.success("Successfully update the order",false,5);

					_table.find("tr").each(function(i) {
						$(this).removeClass("table-listing-row-odd").removeClass("table-listing-row-even");
						i % 2 ? $(this).addClass("table-listing-row-odd") : $(this).addClass("table-listing-row-even");
					});

				},"json");
			}
		});
{/if}

		$(this).find(".{$hyphen_model}-remove").off().on("click",function() {
			if(confirm("Are you sure you would like to delete this {$lower_task}?"))
				$.post("/{$lower_controller}/do{$lower_task}remove/", { {$id}: $(this).data("{$id}") },
																		function(response) {
																			$("#{$hyphen_model}-list").trigger("load");
																		});

		});

	}).trigger("load");

	$(".{$hyphen_model}-search-interface").off().on("change",function() { $("#{$hyphen_model}-list").trigger("load") });

{if $is_search_form}
	$("#{$hyphen_model}-search-keyword").autocomplete({
		minLength: 0,
		source: [],
		search: function() { $("#{$hyphen_model}-list").trigger("load") }
	});
{/if}
});
</script>

