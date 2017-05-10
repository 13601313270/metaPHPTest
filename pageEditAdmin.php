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
    $page->file = $_GET['file'];

    $metaApi = new phpInterpreter(file_get_contents('./http/'.$_GET['file']));
    $PageObj = $metaApi->search('.= [className=kod_web_page]')->parent()->toArray();
    $PageObj = $PageObj[0]['object1']['name'];

    //用到的tpl文件
    $tplFile = $metaApi->search('.objectFunction:filter(#fetch) object:filter([name='.$PageObj.'])')->parent()->toArray();
    $tplFile = $tplFile[0]['property'][0]['data'];
    $page->tplFile = $tplFile;

    //使用的GET参数
    $allGet = $metaApi->search('.arrayGet object:filter([name=$_GET])')->parent()->toArray();
    $allKeys = array();

    //执行脚本,计算出所有推送到前端的变量
    $allPushParams = $metaApi->search('.objectParams object:filter(#'.$PageObj.')')->parent()->parent()->toArray();
    foreach($allPushParams as $k=>$v){
        $allPushParams[$k] = array(
            'type'=>'returnEvalValue',
            'key'=>array(
                'type'=>'string',
                'data'=>$v['object1']['name']
            ),
            'value'=>$v['object2'],
        );
    }
    $tplFile = $metaApi->search('.objectFunction:filter(#fetch) object:filter([name='.$PageObj.'])')->parent()->toArray();//删除fetch输出调用
    $tplFile[0] = null;
    $evalObj = new evalMetaCode($metaApi->codeMeta,array(
        'id'=>1,
    ));
    $page->pushResult = $evalObj->run();

    foreach($allGet as $v){
        if(!in_array($v['key']['data'],$allKeys)){
            $allKeys[] = $v['key']['data'];
        }
    }
    $page->allGet = $allKeys;

    $page->tplFileContent = file_get_contents('./http/'.$page->tplFile);
}
$page->fetch('pageEditAdmin.tpl');
