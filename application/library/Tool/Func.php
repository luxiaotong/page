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

    static function ini2array($ini, $seperator = ".", &$arr = NULL){
        foreach($ini as $k => $v){
            do{
                $dotpos = strrpos($k, $seperator);
                if($dotpos === false){
                    $arr[$k] = $v;
                    break;
                }else{
                    $v = array(substr($k, $dotpos + 1) => $v);
                    $k = substr($k, 0, $dotpos);
                }
            }while(true);
        }
        return $arr;
    }
}
