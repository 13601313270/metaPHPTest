<?php

/**
 * Created by PhpStorm.
 * User: 王浩然
 * Date: 2017/3/20
 * Time: 下午2:14
 */

include_once('include.php');
$page=new kod_web_page();
if(in_array($_GET['file'],scandir('./http/'))){
    $metaApi = new phpInterpreter(file_get_contents('./http/'.$_GET['file']));
    $PageObj = $metaApi->search('.= [className=kod_web_page]')->parent()->toArray();
    $PageObj = $PageObj[0]['object1']['name'];

    $tplFile = $metaApi->search('.objectFunction:filter(#fetch) object:filter([name='.$PageObj.'])')->parent()->toArray();
    $tplFile = $tplFile[0]['property'][0]['data'];
    $page->tplFile = $tplFile;
    $page->file = $_GET['file'];
    $page->tplFileContent = file_get_contents('./http/'.$tplFile);
}
$page->fetch('pageEditAdmin.tpl');
