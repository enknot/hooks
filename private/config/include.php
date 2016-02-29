<?php

//error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


date_default_timezone_set('America/New_York');

define('LOG_ROOT', '/var/www/html/alliance/private/logs');

/**
 * GLOBAL FUNCTIONS
 * Example global function prints an object inside of pre tags for easy debugging with line returns.  You should not have very
 * many of these.  Think, what do you need to run everywhere.  Everything is better as static methods on helper classes
 * as they only load when needed instead of always loading.
 *
 */
function print_pre($r, $title = NULL) {
    return "<pre><b>$title</b><br />" . print_r($r, 1) . '</pre>';
}

/**
 * Wraps a list of array elements with single quotes for group SQL insertion
 * @todo escape all single quotes before wrapping
 * @param type $array
 * @return array
 */
function quoteWrap(&$array) {
    foreach ($array as $key => $val) {
        $array[$key] = "'" . str_replace('\'', '\\\'', $val) . "'";
    }
    return $array;
}

/**
 * Formats array keys for PDO paramter biz
 * @param type $array
 * @return type
 */
function paramify($array) {
    $ret = array();

    if (!is_array($array))
        die(print_pre($array, gettype($array)));

    foreach ($array as $key => $value) {

        if ($key[0] != ':') {
            $ret[':' . $key] = $value;
        } else {
            $ret[$key] = $value;
        }
    }
    return $ret;
}

function cleanParams($array, $parmamify = true) {
    $ret = [];
    foreach ($array as $index => $item) {
        if (trim($item) != '') {
            $ret[$index] = $item;
        }
    }
    return ($parmamify) ? paramify($ret) : $ret;
}

/**
 * Formats array keys for PDO paramter biz
 * @param type $array
 * @return type
 */
function deramify($array) {
    $ret = array();

    foreach ($array as $key => $value) {

        if ($key[0] == ':') {
            $ret[substr_replace($key, '', 0, 1)] = $value;
        } else {
            $ret[$key] = $value;
        }
    }
    return $ret;
}

/**
 * 
 * @param type $params
 * @param type $requisits
 */
function array_keys_exist($params, $requisits, &$missing = array()) {
    $keys = array_keys($params);
    $missing = array();
    $ret = true;
    foreach ($requisits as $req) {
        if (!in_array($req, $keys)) {
            $ret = false;
            $missing[] = $req;
        }
    }

    return $ret;
}

/**
 * Sorts the objects in reverse order base on an order element
 * @param type $a
 * @param type $b
 * @return type
 */
function reverseOrderSort($a, $b) {
    if (is_array($a)) {
        return $b['order'] - $a['order'];
    } else {
        return $b->order - $a->order;
    }
}

/**
 * Sorts the objects in order base on an order element
 * @param type $a
 * @param type $b
 * @return type
 */
function orderSort($a, $b) {
    if (is_array($a)) {
        if ($a['order'] == $b['order'])
            return 0;
        return ($a['order'] < $b['order']) ? -1 : 1;
    }else {
        if ($a->order == $b->order)
            return 0;
        return ($a->order < $b->order) ? -1 : 1;
    }
}

function currentTimestamp() {
    return date(MYSQL_TIMESTAMP_FORMAT);
}

function logger($type, $data) {

    $dir = LOG_ROOT . '/' . $type . '/';
    $str = print_r($data, 1);

    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    
    $file_name = $dir . date('Y_m_d') . '.log';
    $file_data = "\n\n" . date('Y/m/d H:i:s') . "\n" . $str;
    
    if(file_exists($file_name)){
        $file_data .= file_get_contents($file_name);
    }
    
    file_put_contents($file_name, $file_data);
}

function ls($dir) {
    
    if ($handle = opendir($dir)) {

        $blacklist = array('.', '..');

        $ret = [];
        while (false !== ($file = readdir($handle))) {

            if (!in_array($file, $blacklist)) {
                $ret[] = "$file\n";
            }
        }

        return $ret;
    }
}
