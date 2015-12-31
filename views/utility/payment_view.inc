<?
	class VIEW_UTILITY_PAYMENT extends VIEW {
		
		const PAYMENT_TYPE_ON_DEMAND_CREATE	= "odc";
		const PAYMENT_TYPE_ON_DEMAND_PAYMENT = "odp";
		const PAYMENT_TYPE_SEND = "snd";
		const PAYMENT_TYPE_PREAPPROVAL = "pre";
		
		protected $_payment_api_username 	= "";
		protected $_payment_api_password 	= "";
		protected $_payment_signature 		= "";
		protected $_payment_type			= "";
		protected $_payment_preference	 	= "";
		protected $_payment_reference_id 	= "";
		protected $_payment_amount		 	= "";
		
		function __construct() {
			$this->set_route("utility","payment");
			$this->set_form("/utility/dopayment/");
			$this->set_roles(array(SECURITY_MANAGER::ROLE_PUBLIC_USER));


			$payment_credentials = JSON_UTIL::decode(HTML_COOKIE_UTIL::get_cookie("payment_credentials"));

			$this->_payment_api_username 	= get_value($payment_credentials,"api_username");
			$this->_payment_api_password 	= get_value($payment_credentials,"api_password");
			$this->_payment_signature 	= get_value($payment_credentials,"signature");

		}
		
		function init() {

			$payment_type_list = array(	
							self::PAYMENT_TYPE_ON_DEMAND_CREATE=>"On Demand Creation",
							self::PAYMENT_TYPE_ON_DEMAND_PAYMENT=>"On Demand Payment",
							self::PAYMENT_TYPE_PREAPPROVAL=>"Get Preapproval",
							self::PAYMENT_TYPE_SEND=>"Send");

			$this->set_var("payment_type_list",$payment_type_list);
			$this->set_var("payment_api_username",$this->_payment_api_username);
			$this->set_var("payment_api_password",$this->_payment_api_password);
			$this->set_var("payment_signature",$this->_payment_signature);
			$this->set_var("payment_type",$this->_payment_type);
			$this->set_var("payment_preference",$this->_payment_preference);
			$this->set_var("payment_reference_id",$this->_payment_reference_id);
			$this->set_var("payment_amount",$this->_payment_amount);
			
			
		}	
		
		function set_payment_api_username($payment_api_username) 		{ $this->_payment_api_username = $payment_api_username; }
		function set_payment_api_password($payment_api_password) 		{ $this->_payment_api_password = $payment_api_password; }
		function set_payment_signature($payment_signature) 				{ $this->_payment_signature = $payment_signature; }
		function set_payment_type($payment_type) 						{ $this->_payment_type = $payment_type; }
		function set_payment_preference($payment_preference) 			{ $this->_payment_preference = $payment_preference; }
		function set_payment_reference_id($payment_reference_id) 		{ $this->_payment_reference_id = $payment_reference_id; }
		function set_payment_amount($payment_amount)					{ $this->_payment_amount = $payment_amount; }
	}