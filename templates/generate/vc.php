<h1>Frontend View / Controller</h1>
<form id="form-vc">
	<div class="dib">
		<div class="fl">
			<?
				HTML_FORM_UTIL::create()
						->custom("<h3>Primary Interface</h3>")
						->input("form1[url]","URL","",false,["placeholder"=>"ie. /manage/users","class"=>"w250","data-form"=>"form1"])
						->input("form1[state]","State Name","",true,["placeholder"=>"ie. page.userprofile","class"=>"w250"])
						->input("form1[controller]","Controller Name","",true,["placeholder"=>"ie. UserProfile","class"=>"w250","data-form"=>"form1"])
						->input("form1[view]","Template  File Name","",true,["placeholder"=>"ie. userprofile","class"=>"w250","data-form"=>"form1"])
						->dropdown("form1[view_format]","Display On",["page"=>"Full Page","modal"=>"Modal","drawer"=>"Drawer"],"")
						->dropdown("form1[interface]","Include Interface",[""=>"None","lister"=>"Lister","form"=>"Form"],"")
						->input("form1[object]","Object Name","",true,["placeholder"=>"ie. user","class"=>"w250 lister1-option form1-option"])
						->checkboxes("form1[options]","Lister Options",["secondary"=>"Create Secondary Interface"],["secondary"],false,["placeholder"=>"ie. user","class"=>"lister1-option"])
						->checkboxes("form1[options]","Form Options",["draft"=>"Apply draft pattern"],"",false,["placeholder"=>"ie. user","class"=>"form1-option"])
						->render();
			?>
		</div>

		<div class="fl dn pl50" id="form2">
			<?
				HTML_FORM_UTIL::create()
						->custom("<h3>Secondary Interface</h3>")
						->input("form2[url]","URL","","",["placeholder"=>"ie. /manage/users/:id","class"=>"w250","data-form"=>"form2"])
						->input("form2[state]","State Name","",true,["placeholder"=>"ie. page.userprofile","class"=>"w250"])
						->input("form2[controller]","Controller Name","",true,["placeholder"=>"ie. UserProfile","class"=>"w250","data-form"=>"form2"])
						->input("form2[view]","Template  File Name","",true,["placeholder"=>"ie. userprofile","class"=>"w250","data-form"=>"form2"])
						->dropdown("form2[view_format]","Display On",["page"=>"Full Page","modal"=>"Modal","drawer"=>"Drawer"],"")
						->dropdown("form2[interface]","Include Interface",["form"=>"Form"],"")
						->checkboxes("form2[options]","Form Options",["draft"=>"Apply draft pattern"],"",false,["placeholder"=>"ie. user","class"=>"form2-option"])
						->render();
			?>
		</div>

		<div class="fl pl50">
			<?
				HTML_FORM_UTIL::create()
						->custom("<h3>Primary Summary</h3>")
						->text("Controller Class",HTML_UTIL::div("",["id"=>"form1-controller-classname"]))
						->text("Controller Filename",HTML_UTIL::div("",["id"=>"form1-controller-filename"]))
						->text("View Filename",HTML_UTIL::div("",["id"=>"form1-view-filename"]))
						->custom("<h3>Secondary Summary</h3>",["class"=>"secondary-summary"])
						->text("Controller Class",HTML_UTIL::div("",["id"=>"form2-controller-classname","class"=>"secondary-summary"]))
						->text("Controller Filename",HTML_UTIL::div("",["id"=>"form2-controller-filename","class"=>"secondary-summary"]))
						->text("View Filename",HTML_UTIL::div("",["id"=>"form2-view-filename","class"=>"secondary-summary"]))
						->render();
			?>
		</div>

		<div class="cb tac pt30">
			<button type="button" class="btn btn-primary" id="generate">Generate</button>
		</div>
	</div>
</form>

<script>

	String.prototype.capitalize = function(){
       return this.replace( /(^|\s)([a-z])/g , function(m,p1,p2){ return p1+p2.toUpperCase(); } );
    };

	$(function() {

		$("input[name='form1[url]']").on("keyup input",function(e) {
			$(this).val(url($(this).val()));

			var parts = $(this).val().split("/");
			var form = $(this).data("form");

			var names = $.merge([], parts);

			$.each(names,function(key,value) {
				names[key] = value.capitalize();
			});

			$("input[name='" + form + "[controller]'").val(names.join('')).trigger("keyup");
			$("input[name='" + form + "[view]']").val(parts.join('')).trigger("keyup");
			$("input[name='" + form + "[state]']").val(parts.join('')).trigger("keyup");
			$("input[name='" + form + "[object]']").trigger("keyup");
		});

		$("select[name='form1[interface]']").on('change',function() {
			$(".lister1-option,.form1-option").parents("tr").hide();

			if($(this).val()=="lister") {
				$(".lister1-option").parents("tr").show();
			} else if($(this).val()=="form") {
				$(".form1-option").parents("tr").show();
			}

			if($(this).val()=="lister" && $("input[name='form1[options][]'][value='secondary']:checked").length) {
				$("#form2").show();
				$(".secondary-summary").parents("tr").show();
			} else {
				$("#form2").hide();
				$(".secondary-summary").parents("tr").hide();
			}
		}).trigger('change');

		$("input[name='form1[secondary]']").on('change',function() {
			$("select[name='form1[interface]']").trigger("change");
		}).trigger('change');

		$("input[name='form1[controller]'],input[name='form2[controller]']").on("keyup",function(e) {

			$("#" + $(this).data("form") + "-controller-classname").text($(this).val() + "Ctrl");
			$("#" + $(this).data("form") + "-controller-filename").text("/scripts/controllers/" + $(this).val().toLowerCase() + ".js");

			$("input[name='" + $(this).data("form") + "[view]']").trigger("keyup");

			if($(this).data("form")=="form1") {
				$("input[name='form2[controller]']").val(singular($(this).val())).trigger("keyup");
			}
		});

		$("input[name='form1[view]'],input[name='form2[view]']").on("keyup",function(e) {
			$("#" + $(this).data("form") + "-view-filename").text("/views/" + $(this).val() + ".html");

			if($(this).data("form")=="form1") {
				$("input[name='form2[view]']").val(singular($(this).val())).trigger("keyup");
			}
		});

		$("input[name='form1[state]']").on("keyup",function(e) {
			$("input[name='form2[state]']").val(singular($(this).val()));
		});

		$("input[name='form1[object]']").on("keyup",function(e) {
			$("input[name='form2[url]']").val($("input[name='form1[url]']").val() + "/:id").trigger("keyup");
		});
	});

	$("input[name='form1[options][]']").on('change',function() {
		$("select[name='form1[interface]']").trigger("change");
	});

	$("input[name='form2[url]']").on("keyup input",function(e) {
		$(this).val(url($(this).val()));
	});


	$("#generate").click(function() {
		$.post("/generate/dovc",$("#form-vc").serializeArray(),function(response) {

			if(response.has_success) {
				FF.msg.success('Successfully generated');
			} else
				FF.msg.error(response.errors);

			if(response.data.warnings.length)
				FF.msg.warning(response.data.warnings,{ append: true });
		});
	});

	function singular(value) {
		return value.replace(/ies$/,'y').replace(/s$/,'');
	}

	function url(value) {
		if(value) {
			return '/' + value.replace(/(^\/|\/$)*/,'');
		}
	}

</script>
