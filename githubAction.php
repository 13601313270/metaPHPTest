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
include_once('./metaPHP/classAction.php');
class temp extends githubAction{
    public $runLocalBranch = 'develop';
    public $webRootDir = '/var/www/html/metaPHPTest';
    public $cachePath = '/var/www/html/metaPHPTest/metaPHPCacheFile';

    public function main(){
        $newBranchName = '父类操作分支';
        $this->createBranch($newBranchName);
        $tempParentClass = new classAction('parentTempClass');
        $tempParentClass->remove();
        $this->commit('删除了刚才创建的通用父类');
        $this->checkout($this->runLocalBranch);
        $this->mergeBranch($newBranchName);
        $this->commit('合并分支:'.$newBranchName.'到'.$this->runLocalBranch);
        $this->deleteBranch($newBranchName);
        $this->push();
    }
    public function run()
    {
        $input = file_get_contents('php://input');
        if(empty($input)){
            //第二次被命令行触发,进入这里
            $this->checkout($this->runLocalBranch);
            $this->branchClean();
            $this->main();
            $this->checkout($this->runLocalBranch);
        }else{
            //第一次触发,进入这里,拉取代码,然后重新跳转到自己,执行新加载的代码
            $response = json_decode($input);
            if(!in_array($response->ref,array(
                'refs/heads/master',
                'refs/heads/develop'
            ))){
                exit;
            }
            if(count($response->commits)==0 || $response->commits[0]->author->name=='metaPHPRobot'){
                exit;
            }
            $this->checkout($this->runLocalBranch);
            parent::pull();
            exec('cd ' .dirname(__FILE__) . ';php '.__FILE__);
        }
    }
}
$a = new temp();
$a->run();