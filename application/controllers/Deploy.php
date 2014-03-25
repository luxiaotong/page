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
        if ( !empty($_POST['srcaddr']) && !empty($_POST['desaddr']) ) { 
            $this->deploy_obj->run_deploy($_POST['srcaddr'], $_POST['desaddr']);
        }
    }

}
