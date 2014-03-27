<?php
class Tool_Func{
    /**
     * change an array to some simple key val 
     * eg. array('a' => array('b' => 'c')); 
     * while return array('a.b' => 'c')
     * 
     * @param array $arr 
     * @param string $key  first level key, empty string default
     * @param array $ini this is result
     *
     * @return array result
     */
    static function array2ini($arr, $seperator = ".", $key = "", &$ini = NULL){
        foreach((array)$arr as $k => $v){
            $this_key = empty($key) ? $k : $key . $seperator . $k;
            if(is_array($v)){
                self::array2ini($v, $seperator, $this_key, $ini);
            }else{
                $ini[$this_key] = $v;
            }
        }
        return $ini;
    }

    /**
     * change some simple key-val array to complex array(multi dimension)
     * 
     * eg. array('a.b.c' => 'c');
     * retrun array('a'=> array('b'=> array('c'=>'c')));
     *
     * @param array $ini
     * @param string $speerator
     * @param array $arr
     *
     * @return array result
     */
    static function ini2array($ini, $seperator = ".", &$arr = NULL){
        foreach($ini as $k => $v){
            $t = &$arr;
            do{
                $dotpos = strpos($k, $seperator);
                if($dotpos === false){
                    $t[$k] = $v;
                    unset($t);
                    break;
                }else{
                    $key = substr($k, 0, $dotpos);
                    $k = substr($k, $dotpos + 1);

                    $p = &$t;
                    unset($t);
                    $t = &$p[$key];
                }
            }while(true);
        }
        return $arr;
    }
}
