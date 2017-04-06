<?php
;
/**
 * Created by PhpStorm.
 * User: 王浩然
 * Date: 2017/3/20
 * Time: 下午2:14
 */
;
include_once('include.php');
$page=new kod_web_page();
$page2=new stdClass();
$page->fileList=scandir('./http/');
$page->httpFileConfig=array('index.php' => '首页111');
$page->fetch('httpAdmin.tpl');
