<?php
class TestController extends Yaf_Controller_Abstract{

    public function indexAction(){
        Yaf_Dispatcher::getInstance()->disableView();




        $pm = new PartnerModel();
        $pm->writeToini($pm->getPageConfigRedis());
        die;
        $a = explode("\n", file_get_contents("/var/www/php-diff/example/a.txt"));
        $b = explode("\n", file_get_contents("/var/www/php-diff/example/b.txt"));

		$sequenceMatcher = new Diff_SequenceMatcher($a, $b);
		$t = $sequenceMatcher->getGroupedOpcodes();

            $op = Tool_Diff::render($a, $b);
        $this->getView()->assign("diff_opcode", $op);
            $this->getView()->display("include/diff.phtml");
    }

}
