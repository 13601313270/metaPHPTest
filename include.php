<?php
/**
 * Created by PhpStorm.
 * User: mfw
 * Date: 2017/1/22
 * Time: 上午1:39
 */
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);
date_default_timezone_set('PRC');
/*
*设置kod环境变量
*/

define("webDIR",dirname(__FILE__)."/http/");//网站根目录
define("KOD_SMARTY_TEMPLATE_DIR",dirname(__FILE__)."/http/");
define("KOD_REWRITE_HTML_LINK",true);
define('KOD_SMARTY_COMPILR_DIR',dirname(__FILE__).'/html/smarty_cache');

define('KOD_MYSQL_SERVER','118.190.95.219');
define('KOD_MYSQL_USER','root');
define('KOD_MYSQL_PASSWORD','1082322$%&whr309568486');
define('KOD_COMMENT_MYSQLDB','dbName');

define("KOD_SMARTY_CSS_DIR",dirname(__FILE__)."/cssCreate/");
define('KOD_SMARTY_CSS_HOST','http://118.190.95.234/metaPHPTest/cssCreate/');

define('KOD_METAPHP_OPEN',false);
include_once('metaPHP/include.php');



spl_autoload_register(function($model){
    $classAutoLoad = array(
        'projectClass' => 'include/project.php',
        'mem' => 'include/mem.php',
        'pvClass' => 'include/pv.php',
        'articleTypeClass' => 'include/articleType.php',
        'articleClass' => 'include/article.php',
        'webObjectbase' => 'include/webObjectbase.php',
    );
    if(isset($classAutoLoad[$model])){
        include_once $classAutoLoad[$model];
    }
});

/*
*加载kod框架
*/
include_once dirname(__FILE__).'/kod/include.php';
//kod_smarty_smarty::$test = false;