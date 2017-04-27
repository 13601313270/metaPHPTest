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
$allMysqlColType = array(
    'boolean'=>array('name'=>'布尔值', 'saveType'=>'tinyint'),
    'tinyint'=>array('name'=>'tinyint', 'saveType'=>'tinyint'),
    'int'=>array('name'=>'数字', 'saveType'=>'int'),
    'bigint'=>array('name'=>'超大数字', 'saveType'=>'bigint'),
//        'real'=>array('name'=>'real', 'saveType'=>'real'),
    'double'=>array('name'=>'双精度小数', 'saveType'=>'double'),
//        'float'=>array('name'=>'小数', 'saveType'=>'float'),
    'image'=>array('name'=>'图片', 'saveType'=>'varchar'),
    'imageQiniu'=>array('name'=>'七牛图片', 'saveType'=>'varchar'),
//        'decimal'=>array('name'=>'decimal', 'saveType'=>'decimal'),
//        'numeric'=>array('name'=>'numeric', 'saveType'=>'numeric'),
    'numeric'=>array('name'=>'numeric', 'saveType'=>'numeric'),
    'varchar'=>array('name'=>'字符串', 'saveType'=>'varchar'),
    'char'=>array('name'=>'固定长度字符串', 'saveType'=>'char'),
//        'binary'=>array('name'=>'binary', 'saveType'=>'binary'),
//        'varbinary'=>array('name'=>'varbinary', 'saveType'=>'varbinary'),
    'varbinary'=>array('name'=>'varbinary', 'saveType'=>'varbinary'),
    'date'=>array('name'=>'日期', 'saveType'=>'date'),
    'time'=>array('name'=>'时间', 'saveType'=>'time'),
    'datetime'=>array('name'=>'日期+时间', 'saveType'=>'datetime'),
    'timestamp'=>array('name'=>'时间戳', 'saveType'=>'timestamp'),
    'year'=>array('name'=>'年份', 'saveType'=>'year'),
//        'tinyblob'=>array('name'=>'tiny二进制', 'saveType'=>'tinyblob'),
//        'blob'=>array('name'=>'二进制', 'saveType'=>'blob'),
//        'mediumblob'=>array('name'=>'medium二进制', 'saveType'=>'mediumblob'),
//        'longblob'=>array('name'=>'long二进制', 'saveType'=>'longblob'),
    'text'=>array('name'=>'长文本', 'saveType'=>'text'),
//        'mediumtext'=>array('name'=>'长文本', 'saveType'=>'mediumtext'),
//        'longtext'=>array('name'=>'长文本', 'saveType'=>'longtext'),
//        'mediumtext'=>array('name'=>'长文本', 'saveType'=>'mediumtext'),
//        'enum'=>array('name'=>'enum', 'saveType'=>'enum'),
    'set'=>array('name'=>'set', 'saveType'=>'set'),
);
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
}elseif($_POST['action']=='showTableAdmin'){
    //获得include文件夹全部接口类梗概信息
    $allIncludeApi = getAllIncludeApi('./include/','kod_db_mysqlSingle','.property:filter(#$tableName) value data');
    //找到这个表对应的接口类
    $metaSearchApi = new metaSearch($allIncludeApi);
    $thisTableApiInfo = $metaSearchApi->search('.kod_db_mysqlSingle:filter([tableName='.$_POST['class'].'])')->toArray();
    if(empty($thisTableApiInfo)){
        echo '接口不存在';exit;
    }

    $thisTableApiInfo = $thisTableApiInfo[0];
    $className = $thisTableApiInfo['className'];

    //所有后台
    $allIncludeApi = getAllIncludeApi('./admin/','kod_web_mysqlAdmin','#getMysqlDbHandle child .new className');
    //找到这个表对应的后台
    $metaSearchApi = new metaSearch($allIncludeApi);
    $thisTableAdminInfo = $metaSearchApi->search('.kod_web_mysqlAdmin:filter([tableName='.$thisTableApiInfo['className'].'])')->toArray();
    //如果不存在创建一个
    if(empty($thisTableAdminInfo)){
        $adminClassName = $thisTableApiInfo['className'].'Admin';
        $newClass = classAction::createClass($adminClassName,'kod_web_mysqlAdmin');
        array_splice($newClass->phpInterpreter->codeMeta['child'],1,0,array(array(
            'type'=>'functionCall',
            'name'=>'include_once',
            'property'=>array(array('type'=>'string','data'=>'../include.php')),
        )));
        $temp = $newClass->phpInterpreter->search('.comments')->toArray();
        $temp[0]['value'] = '*
* 表'.$thisTableApiInfo['className'].'操作后台
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
        $classApi = new $thisTableApiInfo['className']();
        $option = $classApi->showCreateTable();
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
        $gitAction = new githubClass();
        $gitAction->pull();
        file_put_contents('./admin/'.$adminClassName.'.php',$newClass->phpInterpreter->getCode());
        file_put_contents('./admin/'.$adminClassName.'.tpl','{include file="../adminBase.tpl"}
{block name="content"}
{$adminHtml}
{/block}');
        $gitAction->add('--all');
        $gitAction->commit('增加了表'.$thisTableApiInfo['className'].'的后台');
        $gitAction->push();
        $gitAction->branchClean();

        $allIncludeApi = getAllIncludeApi('./admin/','kod_web_mysqlAdmin','#getMysqlDbHandle child .new className');
        //找到这个表对应的后台
        $metaSearchApi = new metaSearch($allIncludeApi);
    }
    $thisTableAdminInfo = $metaSearchApi->search('.kod_web_mysqlAdmin:filter([tableName='.$thisTableApiInfo['className'].'])')->toArray();
    $adminFileName = $thisTableAdminInfo[0]['fileName'];

    $phpInterpreter = new phpInterpreter(file_get_contents('./admin/'.$adminFileName));
    $option = $phpInterpreter->search('.class:filter([extends=kod_web_mysqlAdmin]) .property:filter(#$dbColumn) value child')->toArray();
    $optionLast = array();
    foreach($option[0] as $item){
        $insert = array();
        foreach($item['value']['child'] as $vv){
            if($vv['value']['type']=='int'){
                $insert[$vv['key']['data']] = intval($vv['value']['data']);
            }elseif($vv['value']['type']=='bool'){
                $insert[$vv['key']['data']] = $vv['value']['data']=='true'?true:false;
            }else{
                $insert[$vv['key']['data']] = $vv['value']['data'];
            }
        }
        $optionLast[$item['key']['data']] = $insert;

    }

    $return = array(
        'option'=>$optionLast,
        'allMysqlColType'=>array()
    );
    foreach($allMysqlColType as $k=>$v){
        $return['allMysqlColType'][] = array_merge(array('type'=>$k),$v);
    }
    echo json_encode($return);exit;
}elseif($_POST['action']=='updateTableAdmin'){
    $table = $_POST['table'];
    $option = $_POST['option'];
    //获得include文件夹全部接口类梗概信息
    $allIncludeApi = getAllIncludeApi('./include/','kod_db_mysqlSingle','.property:filter(#$tableName) value data');
    //找到这个表对应的接口类
    $metaSearchApi = new metaSearch($allIncludeApi);
    $thisTableApiInfo = $metaSearchApi->search('.kod_db_mysqlSingle:filter([tableName='.$table.'])')->toArray();
    if(empty($thisTableApiInfo)){
        echo '接口不存在';exit;
    }
    $className = $thisTableApiInfo[0]['className'];
    $classApi = new $className();
    foreach($classApi->showCreateTable() as $columnName=>$v){
        $isChange = false;
        $optionSave = $option[$columnName];
        foreach($option[$columnName] as $kk=>$vv){
            if($kk=='notNull'){
                $vv = $vv=='true';
            }elseif($kk=='dataType'){
                $vv = $allMysqlColType[$vv]['saveType'];
            }
            if($vv!=$v[$kk] && $kk!=='title'){
                $isChange = true;
                var_dump('==========');
                var_dump($kk);
                var_dump($v[$kk]);
                var_dump($vv);
                $optionSave[$kk] = $vv;
            }
        }
        if($isChange){
            $default = isset($optionSave['default'])?$optionSave['default']:$v['default'];
            $sql = 'ALTER TABLE '.$thisTableApiInfo[0]['tableName'].' MODIFY `'.$columnName.'` '.
                $optionSave['dataType'].
                ($optionSave['maxLength']?('('.$optionSave['maxLength'].')'):'').
                ' '.($optionSave['notNull']?'NOT NULL':'').
                ' DEFAULT '.(in_array($optionSave['dataType'],array('int'))?$default:"'".$default."'");
            $data = kod_db_mysqlDB::create(KOD_COMMENT_MYSQLDB)->runsql($sql);
            echo $sql."\n";
            var_dump($data);
        }
    }
    //所有后台
    $allIncludeApi = getAllIncludeApi('./admin/','kod_web_mysqlAdmin','#getMysqlDbHandle child .new className');
    //找到这个表对应的后台
    $metaSearchApi = new metaSearch($allIncludeApi);
    $thisTableAdminInfo = $metaSearchApi->search('.kod_web_mysqlAdmin:filter([tableName='.$className.'])')->toArray();
    if(empty($thisTableAdminInfo)){echo '接口不存在';exit;}
    $oldCode = file_get_contents('./admin/'.$thisTableAdminInfo[0]['fileName']);
    $phpInterpreter = new phpInterpreter($oldCode);
    $className = $phpInterpreter->search('.class:filter([extends=kod_web_mysqlAdmin]) #$dbColumn value child');
    foreach($option as $columnName=>$canshuList){
        $thisColumnInfo = $className->search('key:filter([data='.$columnName.'])')->parent();
        foreach($canshuList as $canshu=>$canshuVal){
            if($canshu=='default' && $canshuVal===''){
                continue;
            }
            $tempData = $thisColumnInfo->toArray();
//            var_dump($canshu."|".$canshuVal);
            $tempApi = new metaSearch($tempData);
            $tempData2 = $tempApi->search('value child key:filter([data='.$canshu.'])')->parent()->toArray();
            if(empty($tempData2)){
                $canshuMeta = $tempApi->search('value child')->toArray();
                if($canshu=='notNull'){
                    $valueType = 'bool';
                }elseif($canshu=='maxLength'){
                    $valueType = 'int';
                }elseif($canshu=='default'){
                    if($canshuList['dataType']=='int'){
                        $valueType = 'int';
                    }elseif($canshuList['dataType']=='bool'){
                        $valueType = 'bool';
                    }else{
                        $valueType = 'int';
                    }
                }else{
                    $valueType = 'string';
                }
                $canshuMeta[0][] = array(
                    'type'=>'arrayValue',
                    'key'=>array('type'=>'string','borderStr'=>'\'','data'=>$canshu),
                    'value'=>array(
                        'type'=>$valueType, 'borderStr'=>'\'', 'data'=>$canshuVal
                    ),
                );
            }
            else{
                if($tempData2[0]['key']['data']=='notNull'){
                    $tempData2[0]['value']['type'] = 'bool';
                }elseif($tempData2[0]['key']['data']=='maxLength'){
                    $tempData2[0]['value']['type'] = 'int';
                }elseif($tempData2[0]['key']['data']=='default'){
                    if($canshuList['dataType']=='int'){
                        $tempData2[0]['value']['type'] = 'int';
                    }elseif($canshuList['dataType']=='bool'){
                        $tempData2[0]['value']['type'] = 'bool';
                    }else{
                        $tempData2[0]['value']['type'] = 'int';
                    }
                }else{
                    $tempData2[0]['value']['type'] = 'string';
                }
                $tempData2[0]['value']['data'] = $canshuVal;
            }
        }
    }
    //提交git
    if($oldCode!==$phpInterpreter->getCode()){
        echo $phpInterpreter->getCode();
        $gitAction = new githubClass();
        $gitAction->pull();
        file_put_contents('./admin/'.$thisTableAdminInfo[0]['fileName'],$phpInterpreter->getCode());
        $gitAction->add('--all');
        $gitAction->commit('修改了表的后台'.$thisTableAdminInfo[0]['fileName']);
        $gitAction->push();
        $gitAction->branchClean();
    }
}