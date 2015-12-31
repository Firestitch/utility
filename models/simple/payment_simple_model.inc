<?

	class MODEL_PAYMENT extends MODEL {

		public $_method;

		public static function create_processor($api_username, $api_password, $api_signature) {
			//BASE_CMODEL_PAYMENT alias of /framework/base/models/complex/payment_cmplex > BASE_CMODEL_PAYMENT
			$payment_processor = BASE_CMODEL_PAYMENT::create_paymentspro_processor($api_username, $api_password, $api_signature);
			$payment_processor->set_log_directory(MODEL_PATH::get_logs_directory());

			if (SYSTEM_MANAGER::is_system_mode_production())
				$payment_processor->mode_live();

			return $payment_processor;
		}

	}