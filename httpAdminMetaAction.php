<?php
/**
 * Created by PhpStorm.
 * User: 王浩然
 * Date: 2017/3/20
 * Time: 下午2:14
 */
include_once('include.php');
class githubClass extends githubAction{
    public $runLocalBranch = 'develop';
    public $webRootDir = '/var/www/html/metaPHPTest';
    public $cachePath = '/var/www/html/metaPHPTest/metaPHPCacheFile';
}
$metaApi = new phpInterpreter(file_get_contents('./httpAdmin.php'));
$search = $metaApi->search('.= object2:filter([className=kod_web_page])')->parent()->toArray();
$kod_web_pageObj = $search[0]['object1']['name'];
$httpFileConfig = $metaApi->search('.= object1:filter(.objectParams):filter(#httpFileConfig) object:filter(#'.$kod_web_pageObj.')')->parent()->parent()->toArray();
if($_GET['action']=='rename'){
    if(in_array($_GET['name'],scandir('./http/'))){
        $isHasExist = new metaSearch($httpFileConfig[0]['object2']);
        $isHasExist = $isHasExist->search('child .arrayValue key:filter([data='.$_GET['name'].'])')->parent()->toArray();
        if(count($isHasExist)>0){
            $isHasExist[0]['value']['data'] = $_GET['title'];
        }else{
            $httpFileConfig[0]['object2']['child'][] = array(
                'type'=>'arrayValue',
                'key'=>array(
                    'type'=>'string',
                    'borderStr'=>"'",
                    'data'=>$_GET['name'],
                ),
                'value'=>array(
                    'type'=>'string',
                    'borderStr'=>"'",
                    'data'=>$_GET['title'],
                ),
            );
        }
        $gitAction = new githubClass();
        $gitAction->checkout($gitAction->runLocalBranch);
        $gitAction->branchClean();
        $gitAction->pull();


        $gitAction->commit('一次测试提交');
        $gitAction->push();
        $gitAction->branchClean();
        $gitAction->checkout($gitAction->runLocalBranch);

        print_r($gitAction);exit;
        echo $metaApi->getCode();
    }
}
