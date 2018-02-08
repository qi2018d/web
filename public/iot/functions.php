<?php
/**
 * Created by PhpStorm.
 * User: leo
 * Date: 2018-01-30
 * Time: 오후 8:48
 */
function mac_address_formatter($str_addr){
    if(strlen($str_addr) == 12){
        $str_addr = substr(chunk_split($str_addr, 2, ":"),0,17);
        return $str_addr;
    }
    else {
        $str_addr =  substr(chunk_split($str_addr, 2, ":"),0,17);
        return $str_addr;
    }
}
function mac_address_str2hex($str_addr){
    return str_replace(':', '', $str_addr);
}
