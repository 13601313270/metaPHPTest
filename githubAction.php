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
    public $runLocalBranch = 'develop';
    public $webRootDir = '/var/www/html/metaPHPTest';
    public $cachePath = '/var/www/html/metaPHPTest/metaPHPCacheFile';

    public function main(){
        $newBranchName = '去掉了ceshi.txt';
        $this->createBranch($newBranchName);
        unlink($this->webRootDir.'/ceshi.txt');
        $this->commit('删除了ceshi.txt');
        $this->checkout($this->runLocalBranch);
        $this->mergeBranch($newBranchName);
        $this->push();
    }
    public function run()
    {
        $response = json_decode(file_get_contents('php://input'));
        if(!in_array($response->ref,array(
            'refs/heads/master',
            'refs/heads/develop'
        ))){
            exit;
        }
        $this->checkout($this->runLocalBranch);
        parent::pull();
        $this->main();
        $this->checkout($this->runLocalBranch);
    }
}
$a = new temp();
$a->run();