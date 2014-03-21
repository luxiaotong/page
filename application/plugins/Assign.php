<?php
class AssignPlugin extends Yaf_Plugin_Abstract {

    public function dispatchLoopStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        $assignParams = array(
            "controllerName" => strtolower($request->getControllerName()),
            "actionName" => strtolower($request->getActionName()),
        );
        Yaf_Dispatcher::getInstance()->initView(APP_PATH . "/application/view")->assign($assignParams);
    }

}
