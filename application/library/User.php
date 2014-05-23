<?php

class User {

	private static $uid;
	private static $user = array();

	private function __Construct() {

	}

	public static function get_login_uid() {

		return self::$uid;
	}

	public static function set_login_uid($uid) {

		self::$uid = $uid;
		return;
	}
	
	public static function set_user($user) {

		self::$user = $user;
		return;
	}
	
	public static function get_username() {

		return self::$user['username'];
	}
}
