<?php
/**
 * Created by PhpStorm.
 * User: 王浩然
 * Date: 2017/1/23
 * Time: 下午2:56
 */
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);
include_once('./include.php');//这个文件加载的是你的网站框架的运行时,metaPHP只有读懂运行时,才能执行编码
include_once('./metaPHP/githubAction.php');
class temp extends githubAction{
    //metaPHP操作的本地分支,metaPHP所有的编码操作都将以这个分支作为核心
    //(metaPHP编写的代码不推荐直接应用在master分支上,而是通过人为检查后再人工合并)
    public $runLocalBranch = 'develop';

    //你的网站的本地目录
    public $webRootDir = '/var/www/html/metaPHPTest';

    //metaPHP将生成一些,不属于你的网站的代码,这个属性定义了这些代码的存储路径
    public $cachePath = '/var/www/html/metaPHPTest/metaPHPCacheFile';
    public function run()
    {
        $response = json_decode(file_get_contents('php://input'));
        if(!in_array($response->ref,array(
            'refs/heads/master',
            'refs/heads/develop'
        ))){
            exit;
        }
        /*
        一个官方建议的操作流程是
        1.切换到develop
        2.pull拉下最新代码
        3.执行一系列的操作,官方demo就是放在main函数中
        4.所有操作完成后,切换回develop分支

        这样可以保证所有操作都是以develop为核心
        */
        $this->checkout($this->runLocalBranch);
        parent::pull();
        $this->main();
        $this->checkout($this->runLocalBranch);
    }
    public function main(){

    }
}
$a = new temp();
$a->run();