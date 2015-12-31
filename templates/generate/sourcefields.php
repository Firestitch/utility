<?php		

	$source_model_column_dd = HTML_UTIL::get_dropdown("source_model_column",$source_model_column_list,$source_model_column,array(),count($source_model_column_list));

	echo HTML_UTIL::get_heading3("Source Field");

	if($source_model_column_list) 
		echo $source_model_column_dd;
	else
		echo "There are no source fields";

