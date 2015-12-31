<?
	class MODEL_DB extends MODEL {
	
		function get_dbo_file($basename,$app_dir) {
			return FILE_UTIL::sanitize_file($app_dir."db/dbo/".strtolower($basename)."_dbo.inc");
		}
		
		function get_dbq_file($basename,$app_dir) {
			return FILE_UTIL::sanitize_file($app_dir."db/dbq/".strtolower($basename)."_dbq.inc");		
		}
		
		function get_dbq_tablename($basename,$app_dir) {
			$dbq = self::get_dbq($basename,$app_dir);
			return $dbq ? $dbq->get_tablename() : "";
		}		
		
		function get_dbq_class($basename) {
			return "DBQ_".strtoupper($basename);
		}
		
		function get_dbo_class($basename) {
			return "DBO_".strtoupper($basename);
		}
		
		function get_dbo($basename,$app_dir) {
			APPLICATION::include_dbo($basename,$app_dir);			
			$db_class = self::get_dbo_class($basename);
			$dbo = new $db_class();
			return $dbo;
		}		
		
		function get_dbq($basename,$app_dir) {
			APPLICATION::include_dbq($basename,$app_dir);			
			$db_class = self::get_dbq_class($basename);
			$dbq = new $db_class();
			return $dbq;
		}		
		
		function get_dbo_columns($basename,$app_dir) {
		
			if(!is_file(self::get_dbo_file($basename,$app_dir)))
				return array();
		
			APPLICATION::include_dbo($basename,$app_dir);
			
			$db_class = "DBO_".strtoupper($basename);
			
			$dbo = new $db_class();

			return $dbo->get_columns();
		}		
	}