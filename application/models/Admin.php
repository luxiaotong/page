<?php

class AdminModel {

	private $uid;	

	public function __construct($uid) {

		$this->uid = $uid;
	}

	public function get_user_list($page, $count) {
		
		$offset = ( $page - 1 ) * $count;
		$sql = sprintf("SELECT `id`, `username`, `nick`, `editor` FROM `TTT_ADMIN_NEW` LIMIT %d, %d", $offset, $count);  
		$user_data = DB_Mysql::get_instance()->get_data($sql);
		
		if ( !empty($user_data) ) {
			return $user_data;
		} else {
			return array();
		}
	}

	public function add_user($username, $nick) {

		$sql = sprintf("INSERT INTO `TTT_ADMIN_NEW` VALUES (`id`, `username`, `nick`)   
	}
}
