<?php
/**
 * 取自https://github.com/chrisboulton/php-diff
 */

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

    /**
     * 比较两个配置数组的不同点
     * 
     * @param array $old
     * @param array $new
     * @param bool $sort 是否按key 进行一次排序
     *
     * @return array diff_opcode
     */
    public static function ini($old, $new, $sort = true, $options = array()){
        if($sort){
            ksort($old);
            ksort($new);
        }
        $a = $b = array();
        foreach($old as $k => $v){
            $a[] = $k . " = \"" . $v . "\"";
        }
        foreach($new as $k => $v){
            $b[] = $k . " = \"" . $v . "\"";
        }
        return self::render($a, $b, $options);
    }

    /**
     * 将两个数组渲染成前端可以识别的数组
     */
    public static function render($old, $new, $options = array()){
        $diff = self::raw($old, $new, $options);
        if($diff){
            $render = new Diff_Render();
            $diff = $render->render($old, $new, $diff);
        }
        return $diff;
    }

    /**
     * 比较两个数组，这两个数组里的数据是对应文件里的每一行
     * 返回原始的diff信息
     *
     * @param array old 比较的第一个字符串，可以理解为文件里的每一行
     * @param array new 另外一个文件
     * @param array options 比较选项，支持的有
     * 'context' => 100,//相同的时候显示多少上下文
     * 'ignoreNewLines' => false,//忽略新行
     * 'ignoreWhitespace' => false,//忽略空格
     * 'ignoreCase' => false//不区分大小写
     *
     * @return array 返回两个文件的比较情况，equal, replace, delete, insert，和相应的行数据
     */
    public static function raw($old, $new, $options = array()){
        $sequenceMatcher = new Diff_SequenceMatcher($old, $new, null, $options);
        if(empty($options['context'])){
            $options['context'] = 100;
        }
        $groupedCodes = $sequenceMatcher->getGroupedOpcodes($options['context']);
        return $groupedCodes;
    }

}
