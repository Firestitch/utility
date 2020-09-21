<?

	class ACTION_UTILITY_PAYMENT extends ACTION {

		protected $_payment_processor = null;

		function __construct() {
			$this->set_roles(array(SECURITY_MANAGER::ROLE_PUBLIC_USER));
		}

		function process() {

			$registry = REGISTRY::get_instance();
			$payment_credentials = $registry->get_request_parm("payment_credentials");

			$payment_api_usename = get_value($payment_credentials, "api_username");
			$payment_api_password = get_value($payment_credentials, "api_password");
			$payment_signature = get_value($payment_credentials, "signature");
			$payment_type = $registry->get_request_parm("type");
			$payment_preference = $registry->get_request_parm("preference");

			$payment_reference_id = $registry->get_request_parm("reference_id");
			$payment_amount = $registry->get_request_parm("amount");

			HTML_COOKIE_UTIL::set_cookie("payment_credentials", JSON_UTIL::encode($payment_credentials));

			$response_cmodel = new BASE_CMODEL_RESPONSE();

			try {
				$this->_payment_processor = MODEL_PAYMENT::create_processor($payment_api_usename, $payment_api_password, $payment_signature);

				if ($payment_type == VIEW_UTILITY_PAYMENT::PAYMENT_TYPE_ON_DEMAND_CREATE) {
					
					$data = $this->process_ondemand_create();

					$response_cmodel->set_data("data", $data);
					$response_cmodel->success();
				} else if ($payment_type == VIEW_UTILITY_PAYMENT::PAYMENT_TYPE_ON_DEMAND_PAYMENT) {

					$data = $this->process_ondemand_payment();

					$response_cmodel->set_data("data", $data);
					$response_cmodel->success();
				} else if ($payment_type == VIEW_UTILITY_PAYMENT::PAYMENT_TYPE_SEND) {

					$data = $this->process_send();

					$response_cmodel->set_data("data", $data);
					if(!array_key_exists('error', $data))
						$response_cmodel->success();
					else{
						foreach($data as $d)
							$response_cmodel->add_error($d);
					}
				} else {
					$response_cmodel->add_error("Invalid payment type");
				}
			} catch (Exception $e) {
				$response_cmodel->add_error($e->getMessage());
			}

			$response_cmodel->render();
		}

		public function process_ondemand_create() {

			$registry = REGISTRY::get_instance();
			$cc_number = $registry->get_post_parm("odc", "cc_number");
			$cc_expiry_month = $registry->get_post_parm("odc", "cc_expiry_month");
			$cc_expiry_year = $registry->get_post_parm("odc", "cc_expiry_year");
			$cc_name = $registry->get_post_parm("odc", "cc_name");

			$request = $this->_payment_processor->create_ondemand_create_request();

			$credit_card = $request->get_credit_card();
			$credit_card->set_name($cc_name);
			$credit_card->set_number($cc_number);
			$credit_card->set_expiry_month($cc_expiry_month);
			$credit_card->set_expiry_year($cc_expiry_year);
			//$credit_card->set_cvv2($credit_card_cmodel->get_cvv2());

			$address = $request->get_bill_address();
			$address->set_name($cc_name);
			//$address->set_address1($address_cmodel->get_address1());
			//$address->set_address2($address_cmodel->get_address2());
			//$address->set_city($address_cmodel->get_city());
			//$address->set_state($address_cmodel->get_state());
			//$address->set_zip($address_cmodel->get_zip());

			$payment_response = $this->_payment_processor->process($request);

			$data["reference_id"] = $payment_response->get_transaction_id();

			return $data;
		}

		public function process_ondemand_payment() {

			$registry = REGISTRY::get_instance();
			$reference_id = $registry->get_post_parm("odp", "reference_id");
			$amount = $registry->get_post_parm("odp", "amount");

			$request = $this->_payment_processor->create_ondemand_payment_request();
			$request->set_reference_id($reference_id);
			$request->set_amount($amount);

			$payment_response = $this->_payment_processor->process($request);

			$data["transaction_id"] = $payment_response->get_transaction_id();

			return $data;
		}

		/*
		public function process_send() {

			$registry = REGISTRY::get_instance();

			// get payment information
			$sender = $registry->get_post_parm('snd', 'sender');
			$receiver = $registry->get_post_parm('snd', 'receiver');
			$amount = $registry->get_post_parm('snd', 'amount');

			$data = $this->_payment_processor->process_request($sender, $receiver, $amount);
			return $data;
		}
		*/


		public function process_send() {

			$registry = REGISTRY::get_instance();

			// get payment information
			$sender = $registry->get_post_parm('snd', 'sender');
			$receiver = $registry->get_post_parm('snd', 'receiver');
			$amount = $registry->get_post_parm('snd', 'amount');

			$request = $this->_payment_processor->create_send_request();
			$request->set_sender($sender);
			$request->set_amount($amount);
			$request->set_receiver($receiver);

			$payment_response = $this->_payment_processor->process($request);

			$data["transactionId"] = $payment_response->paymentInfoList->paymentInfo->transactionId;
			$data["senderTransactionStatus"] = $payment_response->paymentInfoList->paymentInfo->senderTransactionStatus;
			return $data;
		}		

	}