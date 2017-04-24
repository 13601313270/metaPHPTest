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
function getAllIncludeApi($folder,$classType,$classSplitColumn){
    $gitAction = new githubClass();
    $headCommit = $gitAction->exec('git rev-parse HEAD');
    $allIncludeApi = kod_db_memcache::returnCacheOrSave('allIncludeApi:'.$folder.':'.$classType.":".$classSplitColumn,function()use($folder,$headCommit,$classType,$classSplitColumn){
        $fileList = scandir($folder);
        $allDataApi = array(
            'version'=>$headCommit,
            'data'=>array(),
        );
        if(count($fileList)>2){
            foreach($fileList as $file){
                if(!in_array($file,array('.','..'))){
                    $phpInterpreter = new phpInterpreter(file_get_contents($folder.$file));
                    $className = $phpInterpreter->search('.class:filter([extends='.$classType.']) name')->toArray();
                    $table = $phpInterpreter->search('.class:filter([extends='.$classType.']) '.$classSplitColumn)->toArray();
                    $allDataApi['data'][] = array(
                        'fileName'=>$file,
                        'type'=>$classType,
                        'className'=>$className[0],
                        'tableName'=>$table[0],
                    );
                }
            }
        }
        return $allDataApi;
    },0,60*60,function($data)use($headCommit){
        return false;
        return $data['version']==$headCommit;
    });
    return $allIncludeApi;
}
if($_POST['action']=='tables'){
    $api = new kod_db_mysqlDB();
    $data = kod_db_mysqlDB::create(KOD_COMMENT_MYSQLDB)->runsql('show table status');
    foreach($data as $k=>$v){
        $data[$k]['database'] = KOD_COMMENT_MYSQLDB;
    }
    echo json_encode($data);
}elseif($_POST['action']=='getDataApi'){
    $className = $_POST['name'];
    //获得include文件夹全部接口类梗概信息
    $allIncludeApi = getAllIncludeApi('./include/','kod_db_mysqlSingle','.property:filter(#$tableName) value data');
    //找到这个表对应的接口类
    $metaSearchApi = new metaSearch($allIncludeApi);
    $thisTableApiInfo = $metaSearchApi->search('.kod_db_mysqlSingle:filter([tableName='.$className.'])')->toArray();
    //如果没有则创建一个
    if(empty($thisTableApiInfo)){
        $tableInfo = current(kod_db_mysqlDB::create()->runsql('show create table '.$className));
        if(preg_match('/CREATE TABLE ".+?"\s*\(([\S|\s]*)\)$/',$tableInfo['Create Table'],$match)){
            $tableInfo = explode(',',$match[1]);
            $primaryKey = array();//主键
            $dataType = array();
            //查找主键
            foreach($tableInfo as $k=>$v){
                if(preg_match("/[`|\"](\S+)[`|\"] (int|smallint|varchar|tinyint|char|bigint)\((\d+)\)( NOT NULL| DEFAULT NULL)?( DEFAULT '(\S+)'| AUTO_INCREMENT)?( COMMENT '(\S+)')?/",$v,$match)){
                    $dataType[$match[1]] = $match[2];
                    if(!empty($match[5]) && $match[5]==" AUTO_INCREMENT"){
                        $primaryKey = array('name'=>$match[1], 'dataType'=>$match[2],);
                    }
                    break;
                }elseif(preg_match("/[`|\"](\S+)[`|\"] (text|date)( NOT NULL| DEFAULT NULL)?( DEFAULT '(\S+)'| AUTO_INCREMENT)?( COMMENT '(\S+)')?/",$v,$match)){
                    $dataType[$match[1]] = $match[2];
                }elseif(  preg_match("/[`|\"](\S+)[`|\"] timestamp( NOT NULL| DEFAULT NULL)( DEFAULT CURRENT_TIMESTAMP)?( ON UPDATE CURRENT_TIMESTAMP)?( COMMENT '(\S+)')?/",$v,$match)  ){
                    $dataType[$match[1]] = 'date';
                }elseif( preg_match("/PRIMARY KEY \(\"(\S+)\"\)/",$v,$match) ){
                    $primaryKey = array(
                        'name'=>$match[1], 'dataType' => $option[$match[1]]['dataType']
                    );
                }
            }
            if(!empty($primaryKey)){
                //创建表对应的接口类
                $newClass = classAction::createClass($className,'kod_db_mysqlSingle');
                $temp = $newClass->phpInterpreter->search('.comments')->toArray();
                $temp[0]['value'] = '*
* 表'.$className.'操作接口
*
* User: metaPHP
* Date: '.date('Y/m/d').'
* Time: '.date('H:i').'
';
                $newClass->setProperty('tableName', array('type'=>'string','borderStr'=>"'",'data'=>$className), 'protected');
                $newClass->setProperty('key',array('type'=>'string','borderStr'=>"'",'data'=>$primaryKey['name']), 'protected');
                $newClass->setProperty('keyDataType',array('type'=>'string','borderStr'=>"'",'data'=>$primaryKey['dataType']), 'protected');
                //提交git
                $gitAction = new githubClass();
                $gitAction->pull();
                file_put_contents('./include/'.$className.'.php',$newClass->phpInterpreter->getCode());
                $gitAction->add('--all');
                $gitAction->commit('增加了表'.$className.'的操作接口类');
                $gitAction->push();
                $gitAction->branchClean();

                //刷新一下接口列表
                $allIncludeApi = getAllIncludeApi('./include/','kod_db_mysqlSingle','.property:filter(#$tableName) value data');
                $metaSearchApi = new metaSearch($allIncludeApi);
                $thisTableApiInfo = $metaSearchApi->search('.kod_db_mysqlSingle:filter([tableName='.$className.'])')->toArray();
            }
        }
    }
    echo json_encode($thisTableApiInfo[0]);
}elseif($_POST['action']=='showTableColumn'){
    $allTable = kod_db_mysqlDB::create()->runsql('show tables');
    $allTable = kod_tool_array::getNewArrOfArrColumn($allTable,'Tables_in_phoneGap');
    $database = $_POST['database'];
    $className = $_POST['name'];
    if(!in_array($className,$allTable)){
        exit;
    }
    $tableInfo = current(kod_db_mysqlDB::create()->runsql('show create table '.$className));
    if(preg_match('/CREATE TABLE ".+?"\s*\(([\S|\s]*)\)$/',$tableInfo['Create Table'],$match)){
        $tableInfo = explode(',',$match[1]);
        $primaryKey = array();//主键
        $option = array();
        foreach($tableInfo as $k=>$v){
            if(preg_match("/[`|\"](\S+)[`|\"] (int|smallint|varchar|tinyint|char|bigint)\((\d+)\)( NOT NULL| DEFAULT NULL)?( DEFAULT '(\S+)'| AUTO_INCREMENT)?( COMMENT '(\S+)')?/",$v,$match)){
                $option[$match[1]] = array(
                    "dataType"=>$match[2],
                    "maxLength"=>intval($match[3]),
                    "notNull"=>!empty($match[4]),
                    "title"=>empty($match[8])?$match[1]:$match[8],
                );
                if(!empty($match[5]) && $match[5]==" AUTO_INCREMENT"){
                    $primaryKey = array(
                        'name'=>$match[1],
                        'dataType'=>$match[2],
                    );
                    $option[$match[1]]["AUTO_INCREMENT"] = true;
                }
            }elseif(preg_match("/[`|\"](\S+)[`|\"] (text|date)( NOT NULL| DEFAULT NULL)?( DEFAULT '(\S+)'| AUTO_INCREMENT)?( COMMENT '(\S+)')?/",$v,$match)){
                $option[$match[1]] = array(
                    'dataType'=>$match[2],
                    'notNull'=>!empty($match[3]),
                    'title'=>empty($match[7])?$match[1]:$match[7],
                );
            }elseif(  preg_match("/[`|\"](\S+)[`|\"] timestamp( NOT NULL| DEFAULT NULL)( DEFAULT CURRENT_TIMESTAMP)?( ON UPDATE CURRENT_TIMESTAMP)?( COMMENT '(\S+)')?/",$v,$match)  ){
                $option[$match[1]] = array(
                    "dataType"=>'date',
                    "notNull"=>!empty($match[2]),
                    "title"=>"",
                );
            }elseif( preg_match("/PRIMARY KEY \(\"(\S+)\"\)/",$v,$match) ){
                $primaryKey = array(
                    'name'=>$match[1],
                    'dataType' => $option[$match[1]]['dataType']
                );
            }
        }
        $gitAction = new githubClass();

        //获得include文件夹全部接口类梗概信息
        $allIncludeApi = getAllIncludeApi('./include/','kod_db_mysqlSingle','.property:filter(#$tableName) value data');

        //找到这个表对应的接口类
        $metaSearchApi = new metaSearch($allIncludeApi);
        $thisTableApiInfo = $metaSearchApi->search('.kod_db_mysqlSingle:filter([tableName='.$className.'])')->toArray();

        //如果表不存在对应的接口类,则创建一个
        if(empty($thisTableApiInfo)){
            //创建表对应的接口类
            $newClass = classAction::createClass($className,'kod_db_mysqlSingle');
            $temp = $newClass->phpInterpreter->search('.comments')->toArray();
            $temp[0]['value'] = '*
* 表'.$className.'操作接口
*
* User: metaPHP
* Date: '.date('Y/m/d').'
* Time: '.date('H:i').'
';
            $newClass->setProperty('tableName', array('type'=>'string','borderStr'=>"'",'data'=>$className), 'protected');
            $newClass->setProperty('key',array('type'=>'string','borderStr'=>"'",'data'=>$primaryKey['name']), 'protected');
            $newClass->setProperty('keyDataType',array('type'=>'string','borderStr'=>"'",'data'=>$primaryKey['dataType']), 'protected');
            var_dump($newClass->phpInterpreter->getCode());exit;

            $gitAction->pull();
            file_put_contents('./include/'.$className.'.php',$newClass->phpInterpreter->getCode());
            $gitAction->add('--all');
            $gitAction->commit('增加了表'.$className.'的操作接口类');
            $gitAction->push();
            $gitAction->branchClean();

            //刷新一下接口列表
            $allIncludeApi = getAllIncludeApi('./include/','kod_db_mysqlSingle','.property:filter(#$tableName) value data');
            $metaSearchApi = new metaSearch($allIncludeApi);
            $thisTableApiInfo = $metaSearchApi->search('.kod_db_mysqlSingle:filter([tableName='.$className.'])')->toArray();
        }
        $thisTableApiInfo = $thisTableApiInfo[0];

        //所有后台
        $allIncludeApi = getAllIncludeApi('./admin/','kod_web_mysqlAdmin','#getMysqlDbHandle child .new className');
        //找到这个表对应的后台
        $metaSearchApi = new metaSearch($allIncludeApi);
        $thisTableAdminInfo = $metaSearchApi->search('.kod_web_mysqlAdmin:filter([tableName='.$className.'])')->toArray();

        if(empty($thisTableAdminInfo)){
            //如果不存在创建一个
            $adminClassName = $className.'Admin';
            $newClass = classAction::createClass($adminClassName,'kod_web_mysqlAdmin');
            array_splice($newClass->phpInterpreter->codeMeta['child'],1,0,array(array(
                'type'=>'functionCall',
                'name'=>'include_once',
                'property'=>array(array('type'=>'string','data'=>'../include.php')),
            )));
            $temp = $newClass->phpInterpreter->search('.comments')->toArray();
            $temp[0]['value'] = '*
* 表'.$className.'操作后台
*
* User: metaPHP
* Date: '.date('Y/m/d').'
* Time: '.date('H:i').'
';
            $class = $newClass->phpInterpreter->search('.class')->toArray();
            $class[0]['child'][] = array(
                'type'=>'function',
                'public'=>true,
                'name'=>'getMysqlDbHandle',
                'child'=>array(
                    array(
                        'type'=>'return',
                        'value'=>array('type'=>'new', 'className'=>$thisTableApiInfo['className']),
                    ),
                ),
            );
            $newClass->setProperty('smartyTpl', array('type'=>'string','borderStr'=>"'",'data'=>$adminClassName.'.tpl'), 'protected');

            $dbColumn = array('type'=>'array','child'=>array());
            foreach($option as $k=>$v){
                $insert = array(
                    'type' => 'arrayValue',
                    'key'=>array('type'=>'string','borderStr'=>"'",'data'=>$k),
                    'value'=>array('type'=>'array', 'child'=>array()),
                );
                foreach($v as $kk=>$vv){
                    $insert['value']['child'][] = array(
                        'type'=>'arrayValue',
                        'key'=>array('type'=>'string','borderStr'=>"'",'data'=>$kk),
                        'value'=>array('type'=>gettype($vv),'borderStr'=>"'",'data'=>$vv)
                    );
                }
                $dbColumn['child'][] = $insert;
            }
            $newClass->setProperty('dbColumn',$dbColumn, 'protected');

            $class[0]['child'][] = array(
                'type'=>'function', 'public'=>true, 'name'=>'main',
                'child'=>array(
                    array(
                        'type'=>'=',
                        'object1'=>array('type'=>'variable','name'=>'$adminHtml'),
                        'object2'=>array(
                            'type'=>'objectFunction', 'object'=>'$this', 'name'=>'getAdminHtml',
                            'property'=>array(
                                array('type'=>'objectParams', 'object'=>array('name'=>'$this'), 'name'=>'dbColumn',)
                            ),
                        ),
                    ),
                    array(
                        'type'=>'objectFunction','object'=>'$this','name'=>'assign',
                        'property'=>array(
                            array('type'=>'string','data'=>'adminHtml','borderStr'=>"'"),
                            array('type'=>'variable','name'=>'$adminHtml'),
                        ),
                    ),
                ),
            );

            $newClass->phpInterpreter->codeMeta['child'][] = array(
                'type'=>'=',
                'object1'=>array('type'=>'variable', 'name'=>'$adminObj'),
                'object2'=>array('type'=>'new', 'className'=>$adminClassName),
            );
            $newClass->phpInterpreter->codeMeta['child'][] = array(
                'type'=>'objectFunction', 'object'=>'$adminObj', 'name'=>'run',
            );

            //写入文件系统
            $gitAction->pull();
            file_put_contents('./admin/'.$adminClassName.'.php',$newClass->phpInterpreter->getCode());
            file_put_contents('./admin/'.$adminClassName.'.tpl','{include file="../adminBase.tpl"}
{block name="content"}
{$adminHtml}
{/block}');
            $gitAction->add('--all');
            $gitAction->commit('增加了表'.$className.'的后台');
            $gitAction->push();
            $gitAction->branchClean();

            $allIncludeApi = getAllIncludeApi('./admin/','kod_web_mysqlAdmin','#getMysqlDbHandle child .new className');
            //找到这个表对应的后台
            $metaSearchApi = new metaSearch($allIncludeApi);
            $thisTableAdminInfo = $metaSearchApi->search('.kod_web_mysqlAdmin:filter([tableName='.$className.'])')->toArray();
        }
        $thisTableAdminInfo[0]['option'] = $option;
        echo json_encode($thisTableAdminInfo[0]);exit;
    }
}