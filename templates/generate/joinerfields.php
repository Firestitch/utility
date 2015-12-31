<?php		

	$joiner_source_columns_dd = HTML_UTIL::get_dropdown("joiner_source_column",$joiner_columns,$joiner_source_column,array(),count($joiner_columns));
	$joiner_reference_columns_dd = HTML_UTIL::get_dropdown("joiner_reference_column",$joiner_columns,$joiner_reference_column,array(),count($joiner_columns));

	
	if($joiner_columns) {
		
		echo HTML_UTIL::get_div(HTML_UTIL::get_heading3("Source Joiner Field").$joiner_source_columns_dd,array("class"=>" pr10"));
		echo HTML_UTIL::get_div(HTML_UTIL::get_heading3("Reference Joiner Field").$joiner_reference_columns_dd,array("class"=>""));


	} else
		echo "There are no joiner fields";