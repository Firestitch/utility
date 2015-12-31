<?

	class CMODEL_PAYMENT extends MODEL {

		public function process_ondemand_create($credit_card_cmodel) {

			$this->set_credit_card_id($credit_card_cmodel->get_credit_card_id());
			$this->set_type(DBQ_TRANSACTION::TYPE_ONDEMAND_CREATE);

			$exception = null;

			try {
				$payment_processor = MODEL_PAYMENT::create_processor();

				$request = $payment_processor->create_ondemand_create_request();

				$credit_card = $request->get_credit_card();
				$credit_card->set_name($credit_card_cmodel->get_name());
				$credit_card->set_number($credit_card_cmodel->get_number());
				$credit_card->set_expiry_month($credit_card_cmodel->get_expiry_month());
				$credit_card->set_expiry_year($credit_card_cmodel->get_expiry_year());
				$credit_card->set_cvv2($credit_card_cmodel->get_cvv2());

				$address_cmodel = $credit_card_cmodel->get_address();

				$address = $request->get_bill_address();
				$address->set_name($credit_card_cmodel->get_name());
				$address->set_address1($address_cmodel->get_address1());
				$address->set_address2($address_cmodel->get_address2());
				$address->set_city($address_cmodel->get_city());
				$address->set_state($address_cmodel->get_state());
				$address->set_zip($address_cmodel->get_zip());

				$payment_response = $payment_processor->process($request);

				if ($payment_response->has_success()) {
					$this->set_state(DBQ_TRANSACTION::STATE_SUCCESS);
					$this->set_gateway_transaction_id($payment_response->get_transaction_id());

					$credit_card_cmodel->set_reference_id($payment_response->get_transaction_id());
				}
			} catch (PAYMENT_EXCEPTION $exception) {

				$this->set_message($exception->get_message());
				$this->set_gateway_code($exception->get_merchant_code());
				$this->set_gateway_log($exception->get_log());
				$this->set_system_code($exception->get_system_code());
				$this->set_request($exception->get_request());
				$this->set_response($exception->get_response());
			}

			if ($exception)
				throw $exception;

			return true;
		}

	}