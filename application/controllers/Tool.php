<?php
class ToolController extends Yaf_Controller_Abstract{
    public function diffAction(){
        $diff_opcode = Tool_Diff::file(Tool_Request::getStr('f1'), Tool_Request::getStr('f2'), true, array("context" => 3));
        $this->getView()->assign("diff_opcode", $diff_opcode);
    }
    public function jsondiffAction(){
        $c1 = Tool_Request::getRaw("c1", "POST");
        $c2 = Tool_Request::getRaw("c2", "POST");
        $json1 = json_decode($c1, true);
        $json2 = json_decode($c2, true);
        empty($json1) && $json1 = array();
        empty($json2) && $json2 = array();
        $diff_opcode = Tool_Diff::arr($json1, $json2);
        $this->getView()->assign("diff_opcode", $diff_opcode);
        $this->getView()->assign("c1", $c1);
        $this->getView()->assign("c2", $c2);
    }
}
