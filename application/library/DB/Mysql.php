<?php 
class DB_Mysql {

	private static $instance;
	private $link;
	
	public static function get_instance() {
		
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	private function __construct() {
		
		$config = new Yaf_Config_Ini( APP_PATH . '/conf/source.ini', 'mysql');
		$this->link = mysql_connect($config->master->host, $config->master->username, $config->master->password);
		mysql_select_db($config->master->database, $this->link);
		mysql_query("set names 'utf8'"); 
	}

	public function get_data($sql) {
		
		$result = mysql_query($sql, $this->link);
		while ( $row = mysql_fetch_assoc($result) ) {
			$data[] = $row;
		}

		return $data;
	}

	public function get_line($sql) {
		
		$data = $this->get_data($sql);
		return reset($data);
	}

	public function s($str) {
		
		return mysql_real_escape_string($str, $this->link);
	}
}
