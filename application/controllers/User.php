<?php
class UserController extends Yaf_Controller_Abstract {

    private $admin_obj;

    public function init() {

        if ( $this->getRequest()->isXmlHttpRequest() ) {
            Yaf_Dispatcher::getInstance()->disableView();
            header('Content-Type: application/json; charset=utf-8');
        }
        //$this->deploy_obj = new AdminModel();
    }

	public function loginAction() {
		$this->getView()->assign('title', 'login');
	}    

	public function signinAction() {
		
		$uid = User::get_login_uid();
		if ( !empty($uid) ) {
			$rst = array('rst' => 1);
		} else {
			$rst = array('rst' => 0, 'errmsg' => 'login failed');
		}
		die( json_encode($rst) );
	}

	public function logoutAction() {
		
		User::set_login_uid(null);
		Yaf_Session::getInstance()->del('user');
		header("location: /user/login");
	}

}
