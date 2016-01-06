<?php		

	$joiner_source_columns_dd = HTML_UTIL::get_dropdown("joiner_source_column",$joiner_columns,$joiner_source_column,array(),7);
	$joiner_reference_columns_dd = HTML_UTIL::get_dropdown("joiner_reference_column",$joiner_columns,$joiner_reference_column,array(),7);

	
	if($joiner_columns) {
		
		echo HTML_UTIL::get_div(HTML_UTIL::div("Source Joiner Field",array("class"=>"lbl")).$joiner_source_columns_dd,array("class"=>" pr10"));
		echo HTML_UTIL::get_div(HTML_UTIL::div("Reference Joiner Field",array("class"=>"mt15 lbl")).$joiner_reference_columns_dd,array("class"=>""));


	} else
		echo "There are no joiner fields";