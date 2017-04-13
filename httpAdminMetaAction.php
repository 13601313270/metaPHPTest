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
    public $originBranch = 'origin/develop';
    public $webRootDir = '/var/www/html/metaPHPTest';
    public $cachePath = '/var/www/html/metaPHPTest/metaPHPCacheFile';
}
$metaApi = new phpInterpreter(file_get_contents('./httpAdmin.php'));
$search = $metaApi->search('.= object2:filter([className=kod_web_page])')->parent()->toArray();
$kod_web_pageObj = $search[0]['object1']['name'];
$httpFileConfig = $metaApi->search('.= object1:filter(.objectParams):filter(#httpFileConfig) object:filter(#'.$kod_web_pageObj.')')->parent()->parent()->toArray();
if($_POST['action']=='rename'){
    if(in_array($_POST['name'],scandir('./http/'))){
        $isHasExist = new metaSearch($httpFileConfig[0]['object2']);
        $isHasExist = $isHasExist->search('child .arrayValue key:filter([data='.$_POST['name'].'])')->parent()->toArray();
        if(count($isHasExist)>0){
            $isHasExist[0]['value']['data'] = $_POST['title'];
        }else{
            $httpFileConfig[0]['object2']['child'][] = array(
                'type'=>'arrayValue',
                'key'=>array(
                    'type'=>'string',
                    'borderStr'=>"'",
                    'data'=>$_POST['name'],
                ),
                'value'=>array(
                    'type'=>'string',
                    'borderStr'=>"'",
                    'data'=>$_POST['title'],
                ),
            );
        }
        $gitAction = new githubClass();
        echo date('Y-m-d H:i:s')."\n";
        $gitAction->checkout($gitAction->runLocalBranch);
        echo date('Y-m-d H:i:s')."\n";
        $gitAction->branchClean();
        echo date('Y-m-d H:i:s')."\n";
        $gitAction->pull();
        echo date('Y-m-d H:i:s')."\n";
        file_put_contents('./httpAdmin.php',$metaApi->getCode());
        echo date('Y-m-d H:i:s')."\n";
        $gitAction->add('--all');
        $gitAction->commit('修改httpAdmin.php文件配置kod_web_page实例的httpFileConfig属性'.$_POST['name'].'改为'.$_POST['title']);
        echo date('Y-m-d H:i:s')."\n";
        $gitAction->push();
        echo date('Y-m-d H:i:s')."\n";
        $gitAction->branchClean();
        echo date('Y-m-d H:i:s')."\n";
        $gitAction->checkout($gitAction->runLocalBranch);
    }
}
