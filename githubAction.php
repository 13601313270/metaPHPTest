<?php
/**
 * Created by PhpStorm.
 * User: mfw
 * Date: 2017/1/23
 * Time: 下午2:56
 */
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);
include_once('./metaPHP/githubAction.php');
class temp extends githubAction{
    public $webRootDir = '/var/www/html/metaPHPTest';
    public $cachePath = '/var/www/html/metaPHPTest/metaPHPCacheFile';
    protected $listenBranch = array(
        'refs/heads/master',
        'refs/heads/develop'
    );
}
$a = new temp();
$a->run();