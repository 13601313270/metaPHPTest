<?php
/**
 * Created by PhpStorm.
 * User: mfw
 * Date: 2017/6/13
 * Time: 下午8:06
 */
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);
date_default_timezone_set('PRC');
include_once('metaPHP/include.php');
if(!class_exists('PDO')){
    ?>
    <html><head><meta charset="utf-8"/></head>
    <body>
    <p>请安装PDO扩展</p>
    <p>centos7下:yum install php70w-pdo php70w-pdo_dblib php70w-mysqlnd.x86_64<br/>systemctl restart httpd</p>
    </body></html>
    <?php exit;
}elseif(!class_exists('Memcached')){
    ?>
    <html><head><meta charset="utf-8"/></head>
    <body>
    <p>请安装memcached扩展</p>
    <p>centos7下:yum install libmemcached<br/>yum install php70w-pecl-memcached.x86_64<br/>systemctl restart httpd</p>
    </body></html>
    <?php exit;
}elseif($_POST['action']==='option'){
    $errormessage = false;
    try{
        $mysqlCon = new PDO("mysql:host=".$_POST['KOD_MYSQL_SERVER'],
            $_POST['KOD_MYSQL_USER'],
            $_POST['KOD_MYSQL_PASSWORD'],
            array(
                PDO::MYSQL_ATTR_INIT_COMMAND=>"set names utf8"
            )
        );
    }catch (Exception $e){
        $errormessage = '无法连接到mysql,请检查mysql数据库地址,账号,密码是否正确';
    }
    $allDatabase = array();
    $temp = $mysqlCon->query('show databases');
    $temp->setFetchMode(PDO::FETCH_ASSOC);
    foreach ($temp as $row) {
        $allDatabase[] = $row['Database']; //你可以用 echo($GLOBAL); 来看到这些值
    }
    if($_POST['KOD_COMMENT_MYSQLDB']==''){
        $errormessage = '请设置默认数据库';
    }elseif(!in_array($_POST['KOD_COMMENT_MYSQLDB'],$allDatabase)){
        $errormessage = '没有数据库'.$_POST['KOD_COMMENT_MYSQLDB'];
    }
    $memcache_obj = new Memcached();
    $memcache_obj->addServer($_POST['KOD_MEMCACHE_HOST'],$_POST['KOD_MEMCACHE_PORT']);
    $savaRandom = rand(10000,90000);
    $memcache_obj->set('kod_memcache_test',$savaRandom);
    if($memcache_obj->get('kod_memcache_test') !== $savaRandom){
        if(in_array($_POST['KOD_MEMCACHE_HOST'],array('localhost','127.0.0.1'))){
            $errormessage = '请检查本地memcached服务是否安装并开启';
        }else{
            $errormessage = '请检查memcached的ip和端口是否正确';
        }
    }

    $replace = array('KOD_MYSQL_SERVER','KOD_MYSQL_USER','KOD_MYSQL_PASSWORD','KOD_COMMENT_MYSQLDB','KOD_MEMCACHE_HOST','KOD_MEMCACHE_PORT');
    if($errormessage===false){
        $content = file_get_contents('./includeExample.php');
        $phpMetaAPi = new phpInterpreter($content);
        $define = $phpMetaAPi->search('.functionCall:filter(#define)');
        $allConfig = $define->toArray();
        foreach($allConfig as $k=>&$v){
            if(in_array($v['property'][0]['data'],$replace)){
                $v['property'][1]['data'] = $_POST[$v['property'][0]['data']];
            }elseif($v['property'][0]['data']=='KOD_SMARTY_CSS_HOST'){
                $v['property'][1]['data'] = $_SERVER['HTTP_REFERER'].'cssCreate/';
            }
        }
        if(file_put_contents('include.php',$phpMetaAPi->getCode())===false){
            $errormessage = '请开通网站根目录权限给apache,(小白可以粗暴的chmod 777 -R .)';
        }else{
            $result = mkdir('cssCreate');
            $result = mkdir('metaPHPCacheFile');
            $result = mkdir('smarty_cache');
            ?>
            <html>
            <head>
                <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no,minimal-ui">
                <link href="//cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
                <script type="application/javascript" src="//cdn.bootcss.com/jquery/3.2.1/jquery.js"></script>
                <script type="application/javascript" src="//cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
                <style>h1{text-align: center}</style>
            </head>
            <body>
            <h1>配置成功,您可以继续使用了,刷新网页即可跳到首页</h1>
            </body>
            </html>
            <?php
            file_put_contents('rewrite.conf',"/index.php / 301\n/ index.php");
            file_put_contents('index.php',file_get_contents('./kod/demo/singleEntryInclude.php'));
            exit;
        }
    }
}else{
    $errormessage = false;
}
?>
<html>
<head>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no,minimal-ui">
    <link href="//cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <script type="application/javascript" src="//cdn.bootcss.com/jquery/3.2.1/jquery.js"></script>
    <script type="application/javascript" src="//cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <style>
        section{
            width: 80%;margin:0 auto;
        }
        h1{
            text-align: center
        }
        form{
            margin-top: 20px;border-top:solid 1px #6f6f6f;padding-top: 20px;
        }
    </style>
</head>
<body>
<section>
    <?php
    if($errormessage!==false){
        ?>
        <div class="alert alert-danger" role="alert">
            <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
            <span class="sr-only">错误:</span>
            <?php echo $errormessage?>
        </div>
        <?php
    }else{
        ?>
        <h1>欢迎使用,首次进入进行配置</h1>
        <p>您能到达这个界面,意味着服务器环境已经达到了系统要求,接下来需要配置一下系统配置</p>
        <p>本工具使用了如下工具.</p>
        <p>kod:php框架,地址<a href="https://github.com/13601313270/kod" target="_blank">https://github.com/13601313270/kod</a></p>
        <p>metaPHP:php元编程引擎,地址<a href="https://github.com/13601313270/metaPHP" target="_blank">https://github.com/13601313270/metaPHP</a></p>
        <?php
    }
    ?>
    <form class="form-horizontal" method="post">
        <input type="hidden" name="action" value="option">
    </form>
</section>
<script>
    var allNeedOption = {
        'KOD_MYSQL_SERVER':['mysql数据库地址','string','<?php echo $_POST['KOD_MYSQL_SERVER']?$_POST['KOD_MYSQL_SERVER']:'127.0.0.1'?>'],
        'KOD_MYSQL_USER':['mysql调用账号','string','<?php echo $_POST['KOD_MYSQL_USER']?$_POST['KOD_MYSQL_USER']:'root'?>'],
        'KOD_MYSQL_PASSWORD':['mysql调用账号密码','password','<?php echo $_POST['KOD_MYSQL_PASSWORD']?$_POST['KOD_MYSQL_PASSWORD']:''?>'],
        'KOD_COMMENT_MYSQLDB':['mysql操作数据库','string','<?php echo $_POST['KOD_COMMENT_MYSQLDB']?$_POST['KOD_COMMENT_MYSQLDB']:''?>'],
        'KOD_MEMCACHE_HOST':['memcache地址','string','<?php echo $_POST['KOD_MEMCACHE_HOST']?$_POST['KOD_MEMCACHE_HOST']:'localhost'?>'],
        'KOD_MEMCACHE_PORT':['memcache端口','string','<?php echo $_POST['KOD_MEMCACHE_PORT']?$_POST['KOD_MEMCACHE_PORT']:'11211'?>'],
    };
    for(var i in allNeedOption){
        $('form').append('<div class="form-group">'+
            '<label for="inputPassword3" class="col-sm-3 control-label">'+allNeedOption[i][0]+'</label>'+
            '<div class="col-sm-9">'+
            '<input type="'+allNeedOption[i][1]+'" name="'+i+'" class="form-control" id="inputPassword3" placeholder="'+allNeedOption[i][0]+'" value="'+allNeedOption[i][2]+'">'+
            '</div>'+
            '</div>');
    }
    function progress(){
        $('form').after('<div class="progress">'+
            '<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 100%">'+
            '<span class="sr-only">40% Complete (success)</span>'+
            '</div>'+
            '</div>');
    }
    $('form').append('<div class="form-group">'+
        '<div class="col-sm-offset-2 col-sm-10">'+
        '<button type="submit" class="btn btn-default" onclick="progress();">保存</button>'+
        '</div>'+
        '</div>');
</script>
</body>
</html>