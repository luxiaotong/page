<?php

class Tool_Diff{
    /**
     * 要比较两个字符串的不同点，这里是以行为单位比较
     *
     * @param string $s1
     * @param string $s2
     * @param bool $sort  是否将数组排序后再比较,这里所用的算法建议是先排序后再比较，不然会导致结果不太准
     */
    public static function strDiff($s1, $s2, $sort = true){
        //$s1arr = explode('\n', 
    }

    public static function arrDiff($a1, $a2){
        $result = array("add" => array(), "del" => array(), "mod" => array());
        foreach($a1 as $k => $v){
            if(!isset($a2[$k])){
                $result['del'][$k] = $v;
            }elseif($a1[$k] != $a2[$k]){
                $result['mod'][$k] = array($v, $a2[$k]);
            }
        }
        foreach($a2 as $k => $v){
            if(!isset($a1[$k])){
                $result['add'][$k] = $v;
            }
        }
        return $result;
    }

}
