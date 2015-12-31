<?

	class MODEL_GENERATE {
		
		static function get_update_action_template($format)	{ 
			$mapping = array(VIEW_GENERATE_LISTVIEW::FORMAT_AJAX=>"ajax",VIEW_GENERATE_LISTVIEW::FORMAT_POPUP=>"ajax",VIEW_GENERATE_LISTVIEW::FORMAT_PAGE=>"post"); 
			
			return "update_".get_value($mapping,$format)."_action.inc";
		}
		
		static function get_remove_action_template($format)	{ 
			$mapping = array(VIEW_GENERATE_LISTVIEW::FORMAT_AJAX=>"ajax",VIEW_GENERATE_LISTVIEW::FORMAT_POPUP=>"post",VIEW_GENERATE_LISTVIEW::FORMAT_PAGE=>"post"); 
			
			return "remove_".get_value($mapping,$format)."_action.inc";
		}	
		
		static function get_list_view_template($format)	{ 
			$mapping = array("A"=>"ajax","P"=>"post"); 
			
			return "list_".get_value($mapping,$format)."_view.inc";
		}	
		
		static function get_list_template_template($format)	{ 
			$mapping = array("A"=>"ajax","P"=>"post"); 
			
			return "list_".get_value($mapping,$format)."_template.inc";
		}			
		 		
		static function get_abr($field) {
			$parts = array();
			foreach(explode("_",$field) as $part)
				$parts[] = $part=="id" ? "id" : substr($part,0,1);
				
			return implode("",$parts);	
		}	
	}