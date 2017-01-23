<?php
/**
 * Created by PhpStorm.
 * User: 王浩然
 * Date: 2017/1/23
 * Time: 下午2:56
 */
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);
include_once('./include.php');
include_once('./metaPHP/githubAction.php');
class temp extends githubAction{
    public $runLocalBranch = 'develop';//正在运行的本地分支
    public $runBranch = 'refs/heads/develop';//正在运行的本地分支对应的远程分支
    public $webRootDir = '/var/www/html/metaPHPTest';
    public $cachePath = '/var/www/html/metaPHPTest/metaPHPCacheFile';
    protected $listenBranch = array(
        'refs/heads/master',
        'refs/heads/develop'
    );
    public function run()
    {
        if(empty($this->listenBranch)){exit;}
        if(empty($this->webRootDir)){exit;}
        $response = json_decode(file_get_contents('php://input'));
        if(!in_array($response->ref,$this->listenBranch)){exit;}
        parent::pull();
        $this->main();
        $this->checkout($this->runLocalBranch);
    }
    public function main(){
//        $newBranchName = 'metaPHPRobot';
//        $this->deleteBranch($newBranchName);
//        $this->createBranch($newBranchName);
//        mkdir('include');
//        $parentClass = classAction::createClass('tempParentClass','','','autoLoadClass');
//        $parentClass->save();
//        $this->checkout($this->runLocalBranch);
//        $this->mergeBranch($newBranchName);
    }
}
$a = new temp();
$a->run();