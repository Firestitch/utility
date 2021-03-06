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
						->input("form1[options][tabs]","Tabs","",true,["placeholder"=>"ie. tab1,tab2","class"=>"w250"])
						->input("form1[object]","Object Name","",false,["placeholder"=>"ie. user","class"=>"w250 lister1-option form1-option"])
						->dropdown("form1[interface]","Include Interface",[""=>"None","lister"=>"Lister","form"=>"Form"],"")
						->checkboxes("form1[options]","Lister Options",["secondary"=>"Create Secondary Interface",
																		"order"=>"Add ordering interface"],["secondary"],false,["placeholder"=>"ie. user","class"=>"lister1-option"])
						->checkboxes("form1[options]","Form Options",["draft"=>"Apply draft pattern"],"",false,["placeholder"=>"ie. user","class"=>"form1-option"])
						->checkbox("form1[override]","Override existing files")
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
						->input("form2[options][tabs]","Tabs","",true,["placeholder"=>"ie. tab1,tab2","class"=>"w250"])
						->dropdown("form2[interface]","Include Interface",["form"=>"Form"],"")
						->checkboxes("form2[options]","Form Options",["draft"=>"Apply draft pattern"],"",false,["placeholder"=>"ie. user","class"=>"form2-option"])
						->checkbox("form2[override]","Override existing files")
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
						->text("Parameters",HTML_UTIL::div("None",["id"=>"form1-parms"]))
						->hidden("form1[params]","")
						->custom("<h3>Secondary Summary</h3>",["class"=>"secondary-summary"])
						->text("Controller Class",HTML_UTIL::div("",["id"=>"form2-controller-classname","class"=>"secondary-summary"]))
						->text("Controller Filename",HTML_UTIL::div("",["id"=>"form2-controller-filename","class"=>"secondary-summary"]))
						->text("View Filename",HTML_UTIL::div("",["id"=>"form2-view-filename","class"=>"secondary-summary"]))
						->text("Parameters",HTML_UTIL::div("None",["id"=>"form2-parms","class"=>"secondary-summary"]))
						->hidden("form2[params]","")
						->render();
			?>
		</div>

		<div class="cb tac pt30">
			<button type="button" class="btn btn-primary" id="generate">Generate</button>
		</div>
	</div>
</form>

<script>

	$(function() {

		$("input[name='form1[url]'],input[name='form2[url]']").on("keyup input",function(input) {

		    var start = this.selectionStart,
		        end = this.selectionEnd;

			if(this.value) {
				if(!this.value.match(/^\//)) {
					end++;
					start++;
				}

				this.value = ('/' + this.value.replace(/^\//,'').replace(/[^a-z0-9:\/\?_]/,'')).replace(/\/{2,}/,'/');
			}

			this.setSelectionRange(start, end);
		});

		$("input[name='form1[url]']").on("keyup input",function(e) {

			var parsed = parseUrl($(this).val());

			$("#form1-parms").text(parsed.parms.join(', '));
			$("input[name='form1[params]']").val(parsed.parms.join(','));
			$("input[name='form1[controller]'").val(parsed.names.join(''));
			$("input[name='form1[view]']").val(parsed.parts.join(''));
			$("input[name='form1[state]']").val(parsed.parts.join(''));
			$("select[name='form1[view_format]']").trigger('change');
		});


		$("input[name='form2[url]']").on("keyup input",function(e) {

	  		var parsed = parseUrl($(this).val());

			$("#form2-parms").text(parsed.parms.join(', '));
			$("input[name='form2[params]']").val(parsed.parms.join(','));
			$("input[name='form2[controller]'").val(parsed.names.join(''));
			$("input[name='form2[view]']").val(parsed.parts.join(''));
			$("input[name='form2[state]']").val(parsed.parts.join(''));
			$("select[name='form2[view_format]']").trigger('change');
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
		});

		$("input[name='form1[view]'],input[name='form2[view]']").on("keyup",function(e) {
			$("#" + $(this).data("form") + "-view-filename").text("/views/" + $(this).val() + ".html");
		});

		$("select[name='form1[view_format]'],select[name='form2[view_format]']").on("change",function(e) {
			var form = parseFormName($(this).attr("name"));
			var state = $("input[name='" + form + "[state]']").val();
			$("input[name='" + form + "[state]']").val($(this).val() + "." + state.replace(/^(\page\.|modal\.|drawer\.)/,''));
		});

		$("input[name='form1[object]']").on("keyup",function(e) {
			$("input[name='form2[url]']").val($(this).val() + "/:id").trigger("keyup");
		});

		$("input[name='form1[options][]']").on('change',function() {
			$("select[name='form1[interface]']").trigger("change");
		});

		$("#generate").click(function() {
			$.post("/generate/dovc",$("#form-vc").serializeArray(),function(response) {

				if(response.has_success) {
					FF.msg.success('Successfully generated');
				} else
					FF.msg.error(response.errors);

				if(response.data.warning && response.data.warnings.length)
					FF.msg.warning(response.data.warnings,{ append: true });
			});
		});

	});

	function singular(value) {
		return value.replace(/ies$/,'y').replace(/s$/,'');
	}

	function parseUrl(url) {
  		var valuesRegex = new RegExp(':([^:\/]+)', 'g'),
      		matches,
      		parms = [];

	  	while (matches = valuesRegex.exec(url)) {
		    parms.push(matches[1]);
		}

		var parts = url.replace(/:[^\/]*/g,'').split("/");
		var names = $.merge([], parts);

		$.each(names,function(key,value) {
			names[key] = value.capitalize();
		});

		return { parms: parms, names: names, parts: parts };
	}

	function parseFormName(name) {
		return name.match(/(form\d)/)[1];
	}

</script>
