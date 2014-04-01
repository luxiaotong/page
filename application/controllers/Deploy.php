<?php
class DeployController extends Yaf_Controller_Abstract {

    private $deploy_obj;

    public function init() {
        if ( $this->getRequest()->isXmlHttpRequest() ) {
            Yaf_Dispatcher::getInstance()->disableView();
            header('Content-Type: application/json; charset=utf-8');
        }
        $this->deploy_obj = new DeployModel();
    }
    
    public function indexAction() {
        $this->getView()->assign("primary_svn", $this->deploy_obj->get_primary_svn());
        $this->getView()->assign("test_mechine", $this->deploy_obj->get_test_mechine());
    }

    public function sendAction() {
        set_time_limit(0);

        if ( !empty($_POST['srcaddr']) && !empty($_POST['desaddr']) ) { 
            $this->deploy_obj->run_deploy($_POST['srcaddr'], $_POST['desaddr']);
            die(json_encode(array('rst' => 1)));
        }
    }

    public function searchAction() {
        if ( !empty($_POST['q']) ) {
            $srcaddr = $this->deploy_obj->search_srcaddr($_POST['q']);
            $rst = array('rst' => 1, 'srcaddr' => $srcaddr);
            die(json_encode($rst));
        }
    }

}
