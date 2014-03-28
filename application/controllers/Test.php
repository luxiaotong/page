<?php
class TestController extends Yaf_Controller_Abstract{

    public function indexAction(){
        Yaf_Dispatcher::getInstance()->disableView();

        $pm = new PartnerModel();
        $pm->test();
        
    }

}
