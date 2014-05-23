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

	public function add_user($username, $nick, $privs = '') {

		$editor = User::get_username();
		$sql = sprintf("INSERT INTO `TTT_ADMIN_NEW` (`id`, `username`, `nick`, `privs`, `editor`) 
			VALUES (NULL, '%s', '%s', '%s', '%s')", 
			DB_Mysql::get_instance()->s($username), 
			DB_Mysql::get_instance()->s($nick), 
			DB_Mysql::get_instance()->s($privs), 
			DB_Mysql::get_instance()->s($editor)); 
		DB_Mysql::get_instance()->run_sql($sql);
		return;
	}

	public function remove_user($id) {

		$sql = sprintf("DELETE FROM `TTT_ADMIN_NEW` WHERE `id` = %d", $id);
		DB_Mysql::get_instance()->run_sql($sql);
		return;
	}
	
	public function get_user($id) {

		$sql = sprintf("SELECT `id`, `username`, `nick`, `editor` FROM `TTT_ADMIN_NEW` WHERE `id`=%d", $id);  
		$user_data = DB_Mysql::get_instance()->get_line($sql);
		
		if ( !empty($user_data) ) {
			return $user_data;
		} else {
			return array();
		}
	}

	public function modify_user($id, $username, $nick, $privs = '') {

		$editor = User::get_username();
		$sql = sprintf("UPDATE `TTT_ADMIN_NEW` set `username`='%s', `nick`='%s', `privs`='%s', `editor`='%s' WHERE `id`=%d", 
			DB_Mysql::get_instance()->s($username), 
			DB_Mysql::get_instance()->s($nick), 
			DB_Mysql::get_instance()->s($privs), 
			DB_Mysql::get_instance()->s($editor),
			$id); 
		DB_Mysql::get_instance()->run_sql($sql);
		return;
	}
}
