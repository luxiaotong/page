<?php
class UserController extends Yaf_Controller_Abstract {

    private $admin_obj;

    public function init() {

        if ( $this->getRequest()->isXmlHttpRequest() ) {
            Yaf_Dispatcher::getInstance()->disableView();
            header('Content-Type: application/json; charset=utf-8');
        }
	$uid = User::get_login_uid();
        $this->admin_obj = new AdminModel($uid);
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

	public function listAction() {

		$page = Tool_Request::getInt('page', 'get');
		$count = Tool_Request::getInt('count', 'get');
		$page = empty($page) ? 1 : $page;
		$count = empty($count) ? 10 : $count;
		$user_list = $this->admin_obj->get_user_list($page, $count);
		$this->getView()->assign('user_list', $user_list);
	}

	public function additionAction() {

		
	}

	public function addAction() {

		$username = Tool_Request::getStr('username', 'post');
		$nick = Tool_Request::getStr('nick', 'post');
		$this->admin_obj->add_user($username, $nick);
		$rst = array('rst' => 1);
		die( json_encode($rst) );
	}

	public function removeAction() {

		$id = Tool_Request::getInt('id', 'post');
		$this->admin_obj->remove_user($id);
		$rst = array('rst' => 1);
		die( json_encode($rst) );
	}

	public function modificationAction() {

		$id = Tool_Request::getInt('id', 'get');
		$user_data = $this->admin_obj->get_user($id);
		$this->getView()->assign("user", $user_data);
	}
	
	public function modifyAction() {

		$id = Tool_Request::getInt('id', 'post');
		$username = Tool_Request::getStr('username', 'post');
		$nick = Tool_Request::getStr('nick', 'post');
		$this->admin_obj->modify_user($id, $username, $nick);
		$rst = array('rst' => 1);
		die( json_encode($rst) );
	}

}
