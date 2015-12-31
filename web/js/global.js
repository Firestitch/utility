/* ============================================================
 * bootstrap-dropdown.js v2.0.0
 * http://twitter.github.com/bootstrap/javascript.html#dropdowns
 * ============================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================ */


!function(a){function d(){a(b).parent().removeClass("open")}"use strict";var b='[data-toggle="dropdown"]',c=function(b){var c=a(b).on("click.dropdown.data-api",this.toggle);a("html").on("click.dropdown.data-api",function(){c.parent().removeClass("open")})};c.prototype={constructor:c,toggle:function(b){var c=a(this),e=c.attr("data-target"),f,g;if(!e){e=c.attr("href");e=e&&e.replace(/.*(?=#[^\s]*$)/,"")}f=a(e);f.length||(f=c.parent());g=f.hasClass("open");d();!g&&f.toggleClass("open");return false}};a.fn.dropdown=function(b){return this.each(function(){var d=a(this),e=d.data("dropdown");if(!e)d.data("dropdown",e=new c(this));if(typeof b=="string")e[b].call(d)})};a.fn.dropdown.Constructor=c;a(function(){a("html").on("mouseenter.dropdown.data-api",d);a("body").on("mouseover.dropdown.data-api",b,c.prototype.toggle)})}(window.jQuery)



$(document).ready(function() {
	
	$("form").submit(function() {
		$(this).append($("<input>", { 'type': 'hidden', name: "application", value: $("#application").val() }));			
	});

	var update_check = FF.cookie.get("update-check");
	
	expires = new Date();
	expires.setMinutes(expires.getMinutes() + 60);
	
	FF.cookie.set("update-check","1",{ expires: expires });

	$.post("/utility/doupdate",function(response) {
		if(response.has_success && response.data.update)	
			$("#utility-alert").text("IMPORTANT!! There is a newer version of the Utility. Please update your code before continuing to use the Utility.").show();

	});


	$(".update-link").click(function() {
		var url = $(this).data("url");

		if(active_table)
			url += "/table:" + active_table;

		if(active_model)
			url += "/model:" + active_model;

		$(this).attr("href",url);
	});	
});



function get_singular(s) {

	if(s.match(/sses$/))
		return s.replace(/sses$/,'ss');
		
	if(s.match(/y$/))
		return s.replace(/ies$/,'y');
		
	if(s.match(/ies$/))
		return s.replace(/ies$/,'y');			
		
	if(s.match(/s$/))
		return s.replace(/s$/,'');
		
	return s;
} 

var active_table = active_model = "";
function update_links(table, model) {
	if(model)
		active_model = model;

	if(table)
		active_table = table;	
}