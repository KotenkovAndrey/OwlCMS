<?php
function owlstrClear($input, $strip_tags=true){
    if(is_array($input)){
        foreach($input as $key => $string){
            $value[$key] = owlstrClear($string, $strip_tags);
        }
        return $value;
    }
    $string = trim($input);
    if($strip_tags){
        $string = trim(strip_tags($string));
    }
    //Если magic_quotes_gpc = On, сначала убираем экранирование
    $string = (@get_magic_quotes_gpc()) ? stripslashes($string) : $string;
    $string = rtrim($string, ' \\');
    return $string;
}

function owlrequest($var, $type = 'str', $default = false){
    if(isset($_REQUEST[$var])){
        switch($type){
            case 'int': return (int)$_REQUEST[$var]; break;
            case 'str': if($_REQUEST[$var]){return owlstrClear($_REQUEST[$var]);} else {return $default;} break;
            case 'email': if(preg_match("/^([a-zA-Z0-9\._-]+)@([a-zA-Z0-9\._-]+)\.([a-zA-Z]{2,4})$/ui",$_REQUEST[$var])){return $_REQUEST[$var];} else {return $default;} break;
            case 'html': if($_REQUEST[$var]){return owlstrClear($_REQUEST[$var],false);} else {return $default;} break;
            case 'array': if(is_array($_REQUEST[$var])) {foreach($_REQUEST[$var] as $k=> $s){$arr[$k]=owlstrClear($s,false);} return $arr;} else {return $default;} break;
            case 'array_int': if(is_array($_REQUEST[$var])) {foreach($_REQUEST[$var] as $k=> $i){$arr[$k]=(int)$i;} return $arr;}else {return $default;} break;
            case 'array_str': if(is_array($_REQUEST[$var])){foreach($_REQUEST[$var] as $k=> $s){$arr[$k]=owlstrClear($s);} return $arr;}else {return $default;} break;
        }
    }
    else {
        return $default;
    }
}

function owlval($val, $default = ''){
    return $val ? $val : $default;
}

function owlRedirect($url,$code = '303'){
    if($code == '301'){
        header('HTTP/1.1 301 Moved Permanently');
    }
    else{
        header('HTTP/1.1 303 See Other');
    }
    header('Location:' . $url);
    die();
    }

function owldateFormat($date, $is_full_m = true, $is_time = false, $is_now_time = true){
    $date = date('Y-m-d H:i:s', strtotime($date));
    $today = date('Y-m-d', strtotime(date('Y-m-d H:i:s')));
    $yesterday = date('Y-m-d', strtotime(date('Y-m-d H:i:s')) - 86400);
    
    list($day, $time) = explode(' ', $date);
    switch($day){
        case $today:
            $result = 'сегодня';
            if($is_now_time && $time){
                list($h, $m, $s) = explode(':', $time);
                $result .= ' в ' . $h . ':' . $m;
            }
            break;
        case $yesterday:
            $result = 'вчера';
            if($is_now_time && $time){
                list($h, $m, $s) = explode(':', $time);
                $result .= ' в ' . $h . ':' . $m;
            }
            break;
    default:
        list($y, $m, $d) = explode('-', $day);
        $month_full_str = array('января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');
        $month_short_str = array('янв', 'фев', 'мар', 'апр', 'мая', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек');
        $month_int = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
        $day_int = array('01', '02', '03', '04', '05', '06', '07', '08', '09');
        $day_norm = array('1', '2', '3', '4', '5', '6', '7', '8', '9');
        if($is_full_m){
            $m = str_replace($month_int, $month_full_str, $m);
        }
        else{
            $m = str_replace($month_int, $month_short_str, $m);
        }
        $d = str_replace($day_int, $day_norm, $d);
        $result = $d . ' ' . $m . ' ' . $y;
        if($is_time && $time){
            list($h, $m, $s) = explode(':', $time);
            $result .= ' в ' . $h . ':' . $m;
        }
    }
    return $result;
}

function lang($str){
    global $_LANG;
    if(!isset($_LANG[$str])) return $str;
    return $_LANG[$str];
}


function tag($tag = '', $str = '', $class = '', $id = '', $style = ''){
    if($tag == '') return false;
    $html = '<' . $tag;
    if($class != '') $html .= ' class="' . $class . '"';
    if($id != '') $html .= ' id="' . $id . '"';
    if($style != '') $html .= ' style="' . $style . '"';
    $html .= '>' . $str . '</' . $tag . '>';
    return $html;
}

function cutText($text, $length){
    $text = strip_tags($text);
    if(mb_strlen($text) <= $length) return $text;
    $pos = mb_strpos($text, ' ', $length - 5);
    if($pos > 0){
        $text = mb_substr($text, 0, $pos) . '...';
    }
    return $text;
}

function owlJsonInit(){
    return array('error' => false);
}

function owlJsonError($str, $arr = false){
    if($arr and is_array($arr)){
        $arr['error'] = true;
        $arr['reason'] = $str;
        return json_encode($result);    
    }
    $result = array('error' => true, 'reason' => $str);
    return json_encode($result);
}


function getComponent($name){
    global $COMPONENTS;

    if(!isset($COMPONENTS[$name])){
        return false;
    }
    $fname = PATH . "controllers/" . strtolower($COMPONENTS[$name]['controller']) . ".php";
    if(!file_exists($fname)){
        return false;
    }

    require_once $fname;
    $c = new $COMPONENTS[$name]['controller']();
    if(isset($COMPONENTS[$name]['method']) and $COMPONENTS[$name]['method'] != ''){
        $c->setMethod($COMPONENTS[$name]['method']);
    }
    if(isset($COMPONENTS[$name]['template']) and $COMPONENTS[$name]['template'] != ''){
        $c->setTemplate($COMPONENTS[$name]['template']);
    }
    return $c->render();
}  

//functions kit for the test
function varTest($var, $type, $not_null = false, $mustbe = false, $mustcheck = false){
    $result=array(
        'fail'      => false,
        'var'       => $var,
        'type'      => $type,
        'not_null'  => $not_null,
        'mustcheck' => $mustcheck,
        'mustbe'    => $mustbe
    );

    switch($type){
        case 'int':
            if(!is_int($var)){
                $result['fail'] = true;
                $result['reason'] = 'Var is not integer!';
                return $result;             
            }
            break;

        case 'float':
            if(!is_float($var)){
                $result['fail'] = true;
                $result['reason'] = 'Var is not float!';
                return $result;             
            }
            break;

        case 'string':
            if(!is_string($var)){
                $result['fail'] = true;
                $result['reason'] = 'Var is not string!';
                return $result;             
            }
            break;

        case 'bool':
            if(!is_bool($var)){
                $result['fail'] = true;
                $result['reason'] = 'Var is not bool!';
                return $result;             
            }
            break;

        case 'array':
            if(!is_array($var)){
                $result['fail'] = true;
                $result['reason'] = 'Var is not array!';
                return $result;             
            }
            break;
        case 'object':
            if(!is_object($var)){
                $result['fail'] = true;
                $result['reason'] = 'Var is not object!';
                return $result;             
            }
            break;    
    }//end of switch
    if($type != 'bool' and $not_null and empty($var)){
        $result['fail'] = true;
        $result['reason'] = 'Variable is empty!';
        return $result;             
        }
    if(($mustcheck or $mustbe) and $var !== $mustbe){
        $result['fail'] = true;
        $result['reason'] = 'Variable is not equal ' . $mustbe . '!';
        return $result;             
        }
    return $result;
    }

function componentTest($name){
    global $COMPONENTS;
    $result = array("fail" => false);
    if(!isset($COMPONENTS[$name])){
        $result['fail'] = true;
        $result['reason'] = 'Component ' . $name . ' is not set!';
        return $result;
    }
    $comp = $COMPONENTS[$name];

    $fname = PATH . "controllers/" . strtolower($comp['controller'])  .".php";
    if(!file_exists($fname)){
        $result['fail'] = true;
        $result['reason'] = 'File ' . $fname . ' is not exist!';
        return $result;
    }
    require_once $fname;
    $c = new $comp['controller']();
    if(!is_object($c)){
        $result['fail'] = true;
        $result['reason'] = $comp['controller'] . ' is not object!';
        return $result;             
    }
    if(isset($comp['method']) and !method_exists($c, $comp['method'])){
        $result['fail'] = true;
        $result['reason'] = $comp['method'] . ' is not exist in ' . $comp['controller'] . '!';
        return $result;   
    }
    if(isset($comp['template']) and !file_exists(PATH  ."template/" . $comp['template'] . ".php")){
        $result['fail'] = true;
        $result['reason'] = 'Tepmlate ' . $comp['template'] . ' is not exist in ' . $comp['controller'] . '!';
        return $result;   
    }
    return $result;
}        

function printTest($str, $result){
    $class = '';
    if($result['fail']){
        $class = ' class="alert alert-danger"';
        $str .= tag('h4', $result['reason']) . tag('pre', print_r($result, true));
        }
    else {
        $str .= '&nbsp;&nbsp;&nbsp;<span class="label label-success"> OK </span>';
        }
    return '<div' . $class . '>' . $str . '</div>';
    }


?>