<?php
class ToolController extends Yaf_Controller_Abstract{
    public function diffAction(){
        $diff_opcode = Tool_Diff::file(Tool_Request::getStr('f1'), Tool_Request::getStr('f2'), true, array("context" => 3));
        $this->getView()->assign("diff_opcode", $diff_opcode);
    }
}
