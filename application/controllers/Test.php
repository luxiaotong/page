<?php
class TestController extends Yaf_Controller_Abstract{

    public function indexAction(){
        Yaf_Dispatcher::getInstance()->disableView();




        $a = explode("\n", file_get_contents("/var/www/php-diff/example/a.txt"));
        $b = explode("\n", file_get_contents("/var/www/php-diff/example/b.txt"));

		$sequenceMatcher = new Diff_SequenceMatcher($a, $b);
		$t = $sequenceMatcher->getGroupedOpcodes();

            $op = Tool_Diff::render($a, $b);
        $this->getView()->assign("op", $op);
            $this->getView()->display("include/diff.phtml");
    }

}
