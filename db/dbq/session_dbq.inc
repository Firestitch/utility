<?php

	class DBQ_SESSION extends DBQ {

		public function __construct() {
			parent::__construct("sessions", "session_id");
		}
	}