<?php
class LoginPlugin extends Yaf_Plugin_Abstract {

	public function preDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
		$user = Yaf_Session::getInstance()->get('user');
		if ( empty($user) ) { //no login
			
			$username = Tool_Request::getStr('username', 'post');	
			$password = Tool_Request::getStr('password', 'post');	
			$curr_uri = $request->getControllerName() . '/' . $request->getActionName();
			if ( ( empty($username) || empty($password) ) && $curr_uri != 'User/login' ) {
				header("location: /user/login");
			} else if ( !empty($username) && !empty($password) ) {

				//identify from local db
				$sql = sprintf("SELECT `id`, `username`, `nick` FROM `TTT_ADMIN_NEW` WHERE `username`='%s'", DB_Mysql::get_instance()->s($username));
				$user_data = DB_Mysql::get_instance()->get_line($sql);
				if ( empty($user_data) ) {
					return false;
				} else {
					//success
					Yaf_Session::getInstance()->set('user', $user_data);
					User::set_login_uid($user_data['id']);
				}
				 /*else {
					//identify from remote ldap
					$ldap = ldap_connect('10.210.97.21');
					if ( !is_resource($ldap) ) {
						return false;
					}
					$result = ldap_bind($ldap, $user_data['username']."@staff.sina.com.cn", $password);
				}
				if ( !$result ) {
					return false;
				} else {
					//success
					Yaf_Session::getInstance()->set('user', $user_data);
					User::set_login_uid($user_data['id']);
				}*/
			}
		} else {
			
        		$view = Yaf_Dispatcher::getInstance()->initView(APP_PATH . "/application/view");
			$view->assign('user', $user);
		}
	}
}
