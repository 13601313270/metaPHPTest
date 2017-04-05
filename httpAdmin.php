<?php
/**
 * Created by PhpStorm.
 * User: 王浩然
 * Date: 2017/3/20
 * Time: 下午2:14
 */
include_once('include.php');

$content = file_get_contents(__FILE__);
$a = new phpInterpreter(file_get_contents(__FILE__));
//$search = $a->search('.= object2:filter([className=kod_web_page])')->parent()->toArray();

//$kod_web_pageObj = $search[0]['object1']['name'];
//print_r($kod_web_pageObj);
//$search[0] = 1111;
print_r($a->search('.= object1:filter(.objectParams):filter(#httpFileConfig)')->toArray());
exit;

//$searchApi = new metaSearch($searchBase);

//字符.,查找的是type等于某个值的子元素.=就代表 'type'=>'=' ,的元素
//print_r($searchApi->search($searchBase,'.='));

//字符#,查找的是name等于某个值的子元素 #fileList 就代表'name'=>'fileList',的元素
//$re = $searchApi->search('#fileList')->toArray();
//$re = $searchApi->search('.= .new')->parent()->parent()->toArray();
//print_r($re);exit;
//print_r($searchBase);
print_r($content);exit;
$page = new kod_web_page();
$page->fileList = scandir('./http/');
$page->httpFileConfig = array(
    'index.php'=>'首页',
);
$page->fetch('httpAdmin.tpl');