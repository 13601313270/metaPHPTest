<?php
/**
 * Created by PhpStorm.
 * User: mfw
 * Date: 2017/1/22
 * Time: 上午1:39
 */
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);
date_default_timezone_set('PRC');
function autoLoadClass($className){
    return 'include/'.$className.'.php';
}
//闭合


spl_autoload_register(function($class){
    $classPath = autoLoadClass($class);
    include_once($classPath);
});