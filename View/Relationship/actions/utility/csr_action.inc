<?
	class ACTION_UTILITY_CSR extends ACTION_JSON {
	
		function __construct() {
			parent::__construct();
			$this->set_roles(array(SECURITY_MANAGER::ROLE_PUBLIC_USER));
			$this->debug_format_text();
		}
		
		function process() {
			
			$action = $this->request("action");

			$this->_temp_dir = MODEL_PATH::get_temporary_directory();
			FILE_UTIL::mkdir($this->_temp_dir);

			if($action=="generate-key" || $action=="download-key") {

				$cnf_file = MODEL_PATH::get_application_directory()."assets/openssl/openssl.cnf";
				$key_size = $this->request("key_size");
							
				$cmd = "openssl genrsa ".$key_size;

				$has_success = SERVER_UTIL::exec($cmd,$output,true);

				$output = implode("\n",$output);
				
				if(!$has_success)
					throw new Exception($output);
				
				preg_match("/(-+BEGIN RSA PRIVATE KEY-+.*-+END RSA PRIVATE KEY-+)/ism",$output,$match);

				$key = value($match,1);

				if($action=="download-key") {
					HTTP_UTIL::stream_string($key,"generated.key",true);
					FILE_UTIL::rmdir($this->_temp_dir);
					die;
				}
				
				$this->data("value",$key);
				$this->success();
				
			} elseif($action=="generate-csr" || $action=="download-csr") {

				$key 		= $this->request("key");
				$country 	= $this->request("country");
				$city 		= $this->request("city");
				$state		= $this->request("state");
				$company	= $this->request("company");
				$common_name	= $this->request("common_name");

				$key_file = $this->_temp_dir."key.key";

				FILE_UTIL::put($key_file,$key);

				$cnf_file = MODEL_PATH::get_application_directory()."assets/openssl/openssl.cnf";

				$cmd = "openssl req -new -key ".$key_file." -subj \"/C=".$country."/ST=".$state."/L=".$city."/O=".$company."/CN=".$common_name."\" -config ".$cnf_file;

				$has_success = SERVER_UTIL::exec($cmd,$output,true);

				$output = implode("\n",$output);

				if(!$has_success) 
					throw new Exception($output);					

				preg_match("/-----BEGIN CERTIFICATE REQUEST(.*?)END CERTIFICATE REQUEST-----/is",$output,$matches);

				$csr = value($matches,0);

				if($action=="download-csr") {
					HTTP_UTIL::stream_string($csr,"generated.csr",true);
					FILE_UTIL::rmdir($this->_temp_dir);
					die;
				}

				$this->data("value",$csr);
				$this->success($has_success);

			} elseif($action=="generate-crt" || $action=="download-crt") {

				$key 		= $this->request("key");
				$csr 		= $this->request("csr");

				$key_file = $this->_temp_dir."key.key";
				$csr_file = $this->_temp_dir."csr.csr";

				FILE_UTIL::put($key_file,trim($key));
				FILE_UTIL::put($csr_file,trim($csr));

				$cnf_file = MODEL_PATH::get_application_directory()."assets/openssl/openssl.cnf";

				$cmd = "openssl req -x509 -key ".$key_file." -config ".$cnf_file." -in ".$csr_file." -days 365";
				
				$has_success = SERVER_UTIL::exec($cmd,$output,true);

				$output = implode("\n",$output);

				if(!$has_success) 
					throw new Exception($output);

				preg_match("/-----BEGIN CERTIFICATE(.*?)END CERTIFICATE-----/is",$output,$matches);

				$crt = value($matches,0);

				if($action=="download-crt") {
					HTTP_UTIL::stream_string($crt,"generated.crt",true);
					FILE_UTIL::rmdir($this->_temp_dir);
					die;
				}				

				$this->data("value",$crt);
				$this->success($has_success);
				
			} elseif($action=="download-p12") {

				//$pem_file 	= $this->generate_pem();
				$key 		= $this->request("key");
				$crt 		= $this->request("crt");
				$password	= $this->request("p12-password");
				
				$p12_file = $this->_temp_dir."p12.p12";
				$crt_file = $this->_temp_dir."crt.crt";
				$key_file = $this->_temp_dir."key.key";
				
				FILE_UTIL::put($crt_file,trim($crt));
				FILE_UTIL::put($key_file,trim($key));

				$cmd = "openssl pkcs12 -export -inkey ".$key_file." -in ".$crt_file." -out ".$p12_file." -password pass:".$password;

				$has_success = SERVER_UTIL::exec($cmd,$output,true);

				$output = implode("<br>",$output);

				if(!$has_success)
					die($output);

				$p12 = FILE_UTIL::get($p12_file);

				HTTP_UTIL::stream_string($p12,"generated.p12",true);
				FILE_UTIL::rmdir($this->_temp_dir);

				die;				

			} else
				$this->error("Invalid action");

			FILE_UTIL::rmdir($this->_temp_dir);
		}

		function generate_pem() {
			
			$key 		= $this->request("key");
			$csr 		= $this->request("csr");
			$crt 		= $this->request("crt");

			$pem_file = $this->_temp_dir."pem.pem";
			$crt_file = $this->_temp_dir."crt.crt";

			FILE_UTIL::put($pem_file,trim($key));
			FILE_UTIL::put($crt_file,trim($crt));
			
			$cmd = "openssl x509 -inform DER -in ".$crt_file." -outform PEM -out ".$pem_file;
			
			$has_success = SERVER_UTIL::exec($cmd,$output,true);

			$output = implode("\n",$output);

			if(!$has_success)
				throw new Exception($output);

			return $pem_file;
		}
	}