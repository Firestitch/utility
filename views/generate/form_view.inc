<?php

	class VIEW_GENERATE_FORM extends VIEW {

		private $_controller		= "";
		private $_context 		= "";
		private $_task 			= "";
		private $_has_form		= false;
		private $_layer_application	= false;
		private $_layer_core		= true;
		private $_mva_component		= "V";
		private $_remove_view		= "";
		private $_remove_action		= "";
		private $_remove_controller	= "";
		private $_instance		= "core";
		private $_application_only	= false;
		private $_page_number		= "1";

		function __construct() {
			$this->set_route("generate","form");
			$this->set_form("/generate/doform/",true);
			$this->disable_authorization();
		}

		function init() {

			$this->set_var("controller",$this->_controller);
			$this->set_var("context",$this->_context);
			$this->set_var("task",$this->_task);
			$this->set_var("has_form",$this->_has_form);
			$this->set_var("mva_component",$this->_mva_component);
			$this->set_var("remove_view",$this->_remove_view);
			$this->set_var("remove_action",$this->_remove_action);
			$this->set_var("remove_controller",$this->_remove_controller);
			$this->set_var("layer_application",$this->_layer_application);
			$this->set_var("layer_core",$this->_layer_core);
			$this->set_var("page_number",$this->_page_number);			
		}

		function set_controller($value) 	{ $this->_controller = $value; }
		function set_context($value) 		{ $this->_context = $value; }
		function set_task($value) 		{ $this->_task = $value; }
		function set_mva_component($value) 	{ $this->_mva_component = $value; }
		function set_has_form($value) 		{ $this->_has_form = $value; }
		function set_remove_view($value) 	{ $this->_remove_view = $value; }
		function set_remove_action($value) 	{ $this->_remove_action = $value; }
		function set_remove_controller($value) 	{ $this->_remove_controller = $value; }
		function set_instance($value) 		{ $this->_instance = $value; }
		function set_layer_application($value)	{ $this->_layer_application = $value; }
		function set_layer_core($value)		{ $this->_layer_core = $value; }

	}
