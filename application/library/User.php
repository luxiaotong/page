<?php

class User {

	private static $uid;

	private function __Construct() {

	}

	public static function get_login_uid() {

		return self::$uid;
	}

	public static function set_login_uid($uid) {

		self::$uid = $uid;
		return;
	}
}
