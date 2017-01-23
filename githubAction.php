<?php
/**
 * Created by PhpStorm.
 * User: mfw
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
        parent::pull();
        $this->main();
        $this->checkout($this->runLocalBranch);
    }
    public function main(){
        $newBranchName = '创建临时分支';
        $this->createBranch($newBranchName);
        $parentClass = classAction::createClass('tempParentClass','','','autoLoadClass');
        $parentClass->save();
        $this->checkout($this->runLocalBranch);
        $this->mergeBranch($newBranchName);
    }
}
$a = new temp();
$a->run();