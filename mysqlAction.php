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

class control{
    private $allMysqlColType = array(
        'auto_increment'=>array('name'=>'自增数字', 'saveType'=>'auto_increment'),
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
    private function getAllIncludeApi($folder,$classType,$classSplitColumn){
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
    public function tables(){
        $data = kod_db_mysqlDB::create(KOD_COMMENT_MYSQLDB)->runsql('show table status');
        foreach($data as $k=>$v){
            $data[$k]['database'] = KOD_COMMENT_MYSQLDB;
        }
        echo json_encode($data);
    }
    public function getDataApi(){
        $className = $_POST['name'];
        //获得include文件夹全部接口类梗概信息
        $allIncludeApi = $this->getAllIncludeApi('./include/','kod_db_mysqlSingle','.property:filter(#$tableName) value data');
        //找到这个表对应的接口类
        $metaSearchApi = new metaSearch($allIncludeApi);
        $thisTableApiInfo = $metaSearchApi->search('.kod_db_mysqlSingle:filter([tableName='.$className.'])')->toArray();
        //如果没有则创建一个
        if(empty($thisTableApiInfo)){
            $tableInfo = kod_db_mysqlDB::create()->runsql('show create table '.$className);
            if($tableInfo===-1){
                $return = array();
                foreach($this->allMysqlColType as $k=>$v){
                    $return[] = array_merge(array('type'=>$k),$v);
                }
                echo json_encode($return);exit;
            }else{
                $tableInfo = current($tableInfo);
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
                                break;
                            }
                        }elseif(preg_match("/[`|\"](\S+)[`|\"] (text|date)( NOT NULL| DEFAULT NULL)?( DEFAULT '(\S+)'| AUTO_INCREMENT)?( COMMENT '(\S+)')?/",$v,$match)){
                            $dataType[$match[1]] = $match[2];
                        }elseif(  preg_match("/[`|\"](\S+)[`|\"] timestamp( NOT NULL| DEFAULT NULL)( DEFAULT CURRENT_TIMESTAMP)?( ON UPDATE CURRENT_TIMESTAMP)?( COMMENT '(\S+)')?/",$v,$match)  ){
                            $dataType[$match[1]] = 'date';
                        }elseif( preg_match("/PRIMARY KEY \(\"(\S+)\"\)/",$v,$match) ){
                            $primaryKey = array(
                                'name'=>$match[1], 'dataType' => $dataType[$match[1]]
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
                        $newClass->setProperty('keyDataType',array('type'=>'string','borderStr'=>"'",'data'=>in_array($primaryKey['dataType'],array('int','bigint'))?'int':'varchar'), 'protected');
                        //提交git
//                        var_dump(  $newClass->phpInterpreter->getCode()  );exit;
                        $gitAction = new githubClass();
                        $gitAction->pull();
                        file_put_contents('./include/'.$className.'.php',$newClass->phpInterpreter->getCode());
                        $gitAction->add('--all');
                        $gitAction->commit('增加了表'.$className.'的操作接口类');
                        $gitAction->push();
                        $gitAction->branchClean();

                        //刷新一下接口列表
                        $allIncludeApi = $this->getAllIncludeApi('./include/','kod_db_mysqlSingle','.property:filter(#$tableName) value data');
                        $metaSearchApi = new metaSearch($allIncludeApi);
                        $thisTableApiInfo = $metaSearchApi->search('.kod_db_mysqlSingle:filter([tableName='.$className.'])')->toArray();
                    }
                }
            }
        }
        echo json_encode($thisTableApiInfo[0]);
    }
    public function showTableAdmin(){
        $return = array();
        $return['allMysqlColType'] = array();
        foreach($this->allMysqlColType as $k=>$v){
            $return['allMysqlColType'][] = array_merge(array('type'=>$k),$v);
        }
        if(!isset($_POST['class'])){
            echo json_encode($return);exit;
        }

        //获得include文件夹全部接口类梗概信息
        $allIncludeApi = $this->getAllIncludeApi('./include/','kod_db_mysqlSingle','.property:filter(#$tableName) value data');
        //找到这个表对应的接口类
        $metaSearchApi = new metaSearch($allIncludeApi);
        $thisTableApiInfo = $metaSearchApi->search('.kod_db_mysqlSingle:filter([tableName='.$_POST['class'].'])')->toArray();
        if(empty($thisTableApiInfo)){
            echo '接口不存在';exit;
        }

        $thisTableApiInfo = $thisTableApiInfo[0];
        $className = $thisTableApiInfo['className'];

        //所有后台
        $allIncludeApi = $this->getAllIncludeApi('./admin/','kod_web_mysqlAdmin','#getMysqlDbHandle child .new className');
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

            $allIncludeApi = $this->getAllIncludeApi('./admin/','kod_web_mysqlAdmin','#getMysqlDbHandle child .new className');
            //找到这个表对应的后台
            $metaSearchApi = new metaSearch($allIncludeApi);
        }
        $thisTableAdminInfo = $metaSearchApi->search('.kod_web_mysqlAdmin:filter([tableName='.$thisTableApiInfo['className'].'])')->toArray();
        $adminFileName = $thisTableAdminInfo[0]['fileName'];
        $return['adminFileName'] = 'admin/'.$adminFileName;
        $phpInterpreter = new phpInterpreter(file_get_contents('./admin/'.$adminFileName));
        $option = $phpInterpreter->search('.class:filter([extends=kod_web_mysqlAdmin]) .property:filter(#$dbColumn) value child')->toArray();
        $return['option'] = array();
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
            $return['option'][$item['key']['data']] = $insert;

        }
        echo json_encode($return);exit;
    }

    public function getIsExistTable(){
        $tableInfo = kod_db_mysqlDB::create()->runsql('show create table '.$_POST['name']);
        if($tableInfo===-1){
            $return = array();
            foreach($this->allMysqlColType as $k=>$v){
                $return[] = array_merge(array('type'=>$k),$v);
            }
            echo json_encode($return);exit;
        }else{
            echo 'wrong';exit;
        }
    }
    private function getStrByColumnArr($columnName,&$arr){
        if($arr['dataType']=='auto_increment'){
            $arr['dataType'] = 'int';
            $arr['auto_increment'] = true;
            unset($arr['primarykey']);
        }
        if($arr['default']===''){
            unset($arr['default']);
        }
        if(in_array($arr['dataType'],array('int','bigint'))){
            unset($arr['maxLength']);
        }
        if($arr['maxLength']==='NaN'){
            if($arr['dataType']=='varchar'){
                $arr['maxLength'] = 255;
            }
        }
        $arr['unique'] = ($arr['unique']==='true'||$arr['unique']===true);
        if(isset($arr['unique']) && $arr['unique']===false){
            unset($arr['unique']);
        }
        $arr['primarykey'] = ($arr['primarykey']==='true'||$arr['primarykey']===true);
        if(isset($arr['primarykey']) && $arr['primarykey']===false){
            unset($arr['primarykey']);
        }
        $arr['notNull'] = ($arr['notNull']=='true' || $arr['notNull']==true);
        $dataType = $this->allMysqlColType[$arr['dataType']]['saveType'];
        $temp = '`'.$columnName."` ".
            $dataType.
            (isset($arr['maxLength'])?('('.$arr['maxLength'].')'):'').
            ($arr['notNull']?' NOT NULL':'').
            ($arr['default']===null?'': (' DEFAULT '.
                (   in_array($dataType,array('int','bigint'))? $arr['default'] : ('"'.$arr['default'].'"'))
            )).
            (isset($arr['auto_increment'])?" AUTO_INCREMENT":"");
        return $temp;
    }
    public function insertTable(){
        $sql = "CREATE TABLE ".$_POST['table']."(\n";
        $primary = '';
        $unique = array();
        $temp = array();
        foreach($_POST['option'] as $key=>$val){
            $insert = $this->getStrByColumnArr($key,$val);
            $temp[] = $insert;
            if(isset($val['primarykey'])){
                $primary = $key;
            }
            if(isset($val['unique']) && $val['unique']===true){
                $unique[] = $key;
            }
        }
        $sql.=implode(",\n",$temp);
        if(count($unique)>0){
            foreach($unique as $v){
                $sql.=",\nUNIQUE KEY `".$v."` (`".$v."`)";
            }
        }
        if($primary!==''){
            $sql .= ",\nPRIMARY KEY (".$primary.")";
        }
        $sql .= "\n)ENGINE=InnoDB DEFAULT CHARSET=".KOD_COMMENT_MYSQLDB_CHARSET."; ";
        $result = kod_db_mysqlDB::create(KOD_COMMENT_MYSQLDB)->runsql($sql);
        echo $result;exit;
    }
    public function updateTableAdmin(){
        $table = $_POST['table'];
        $option = $_POST['option'];

        //获得include文件夹全部接口类梗概信息
        $allIncludeApi = $this->getAllIncludeApi('./include/','kod_db_mysqlSingle','.property:filter(#$tableName) value data');
        //找到这个表对应的接口类
        $metaSearchApi = new metaSearch($allIncludeApi);
        $thisTableApiInfo = $metaSearchApi->search('.kod_db_mysqlSingle:filter([tableName='.$table.'])')->toArray();
        if(empty($thisTableApiInfo)){
            echo '接口不存在';exit;
        }
        $className = $thisTableApiInfo[0]['className'];
        $classApi = new $className();
        $allDeleteColumn = array();
        $showCreateTable = $classApi->showCreateTable();//数据库中存储的表结构
        foreach($showCreateTable as $columnName=>$dbCanshu){
            if(isset($option[$columnName])){
                $isChange = false;
                $sql = $this->getStrByColumnArr($columnName,$option[$columnName]);
                //查看主键和唯一键是否消失
                foreach (array_diff(array_keys($dbCanshu),array_keys($option[$columnName])) as $item) {
                    if($item=='primarykey'){
                        $dropIndexSql = 'ALTER TABLE `'.$thisTableApiInfo[0]['tableName'].'` DROP primary key';
                        echo $dropIndexSql."\n";
                        var_dump(kod_db_mysqlDB::create(KOD_COMMENT_MYSQLDB)->runsql($dropIndexSql));
                    }elseif($item=='unique'){
                        $dropIndexSql = 'ALTER TABLE `'.$thisTableApiInfo[0]['tableName'].'` DROP INDEX '.$columnName;
                        echo $dropIndexSql."\n";
                        var_dump(kod_db_mysqlDB::create(KOD_COMMENT_MYSQLDB)->runsql($dropIndexSql));
                    }
                }
                foreach($option[$columnName] as $kk=>$vv){
                    if($kk=='dataType' && in_array($vv,array('int','bigint'))){
                        if($option[$columnName]['AUTO_INCREMENT']==true && $dbCanshu['AUTO_INCREMENT']==true  ){
                            if(in_array($dbCanshu[$kk],array('int','bigint'))){
                            }else{
                                echo $kk.";";
                                $isChange = true;
                            }
                        }elseif($option[$columnName]['AUTO_INCREMENT']!==$dbCanshu['AUTO_INCREMENT']){
                            $isChange = true;
                        }elseif($vv!=$dbCanshu[$kk]){
                            $isChange = true;
                        }
                    }elseif($kk=='primarykey'){
                        if($vv!=$dbCanshu[$kk]){
                            $dropIndexSql = 'ALTER TABLE `'.$thisTableApiInfo[0]['tableName'].'` ADD PRIMARY KEY `'.$columnName.'`';
                            echo $dropIndexSql."\n";
                            var_dump(kod_db_mysqlDB::create(KOD_COMMENT_MYSQLDB)->runsql($dropIndexSql));
                        }
                    }else if($vv!=$dbCanshu[$kk] && $kk!=='title'){
                        $isChange = true;
                    }
                }
                if($isChange){
                    $sql = 'ALTER TABLE `'.$thisTableApiInfo[0]['tableName'].'` MODIFY `'.$sql;
                    echo $sql."\n";
                    var_dump(kod_db_mysqlDB::create(KOD_COMMENT_MYSQLDB)->runsql($sql));
                }
            }else{
                $allDeleteColumn[] = $columnName;
                $sql = 'alter table `'.$thisTableApiInfo[0]['tableName'].'` drop column `'.$columnName.'`';
                echo $sql."\n";
                var_dump(kod_db_mysqlDB::create(KOD_COMMENT_MYSQLDB)->runsql($sql));
            }
        }

        //处理新增字段
        $insertColumn = array_diff(array_keys($option),array_keys($showCreateTable));
        foreach($insertColumn as $insertItem){
            $sql = $this->getStrByColumnArr($insertItem,$option[$insertItem]);
            $sql = 'ALTER TABLE '.$thisTableApiInfo[0]['tableName'].' add '.$sql;
            echo $sql."\n";var_dump(kod_db_mysqlDB::create(KOD_COMMENT_MYSQLDB)->runsql($sql));
        }
        //所有后台
        $allIncludeApi = $this->getAllIncludeApi('./admin/','kod_web_mysqlAdmin','#getMysqlDbHandle child .new className');
        //找到这个表对应的后台
        $metaSearchApi = new metaSearch($allIncludeApi);
        $thisTableAdminInfo = $metaSearchApi->search('.kod_web_mysqlAdmin:filter([tableName='.$className.'])')->toArray();
        if(empty($thisTableAdminInfo)){echo '接口不存在';exit;}
        $oldCode = file_get_contents('./admin/'.$thisTableAdminInfo[0]['fileName']);
        $phpInterpreter = new phpInterpreter($oldCode);
        //删除所有不存在的字段
        foreach($allDeleteColumn as $delete){
            $temp = $phpInterpreter->search('.class:filter([extends=kod_web_mysqlAdmin]) #$dbColumn value child key:filter([data='.$delete.'])')->parent()->toArray();
            $temp[0] = null;
        }
        $className = $phpInterpreter->search('.class:filter([extends=kod_web_mysqlAdmin]) #$dbColumn value child');
        foreach($option as $columnName=>$canshuList){
            $thisColumnInfo = $className->search('key:filter([data='.$columnName.'])')->parent();
            $tempData = $thisColumnInfo->toArray();
            if(empty($tempData)){//新增字段
                $dbColumnMetaBase = $phpInterpreter->search('.class:filter([extends=kod_web_mysqlAdmin]) #$dbColumn value child')->toArray();
                $insert = array(
                    'type'=>'arrayValue',
                    'key'=>array('type'=>'string','data'=>$columnName,'borderStr'=>"'"),
                    'value'=>array(
                        'type'=>'array',
                        'child'=>array(),
                    ),
                );
                foreach(array('dataType','notNull','title','maxLength','default') as $canshuName){
                    if($canshuList[$canshuName]!==''){
                        if($canshuName=='dataType'){
                            $canshuList[$canshuName] = 'int';
                            $insert['value']['child'][] = array(
                                'type'=>'arrayValue', 'key'=>array('type'=>'string','data'=>'AUTO_INCREMENT','borderStr'=>"'"),
                                'value'=>array('type'=>'bool','data'=>'true','borderStr'=>"'"),
                            );
                        }elseif($canshuName=='notNull'){
                            $canshuList[$canshuName] = $canshuList[$canshuName]=='true';
                        }elseif($canshuName=='maxLength'){
                            $canshuList[$canshuName] = intval($canshuList[$canshuName]);
                        }elseif($canshuName=='default'){
                            if($canshuList['dataType']=='int'){
                                $canshuList[$canshuName] = intval($canshuList[$canshuName]);
                            }elseif($canshuList['dataType']=='bool'){
                                $canshuList[$canshuName] = $canshuList[$canshuName]=='true'?true:false;
                            }
                        }
                        $insert['value']['child'][] = array(
                            'type'=>'arrayValue', 'key'=>array('type'=>'string','data'=>$canshuName,'borderStr'=>"'"),
                            'value'=>array('type'=>gettype($canshuList[$canshuName]),'data'=>$canshuList[$canshuName],'borderStr'=>"'"),
                        );

                    }
                }
                $dbColumnMetaBase[0][] = $insert;
            }else{//修改字段
                $tempApi = new metaSearch($tempData);
                //如果没有unique,则删除unique属性
                if(!isset($canshuList['unique'])){
                    $tempData2 = $tempApi->search('value child key:filter([data=unique])')->parent()->toArray();
                    $tempData2[0] = null;
                }
                foreach($canshuList as $canshu=>$canshuVal){
                    $tempData2 = $tempApi->search('value child key:filter([data='.$canshu.'])')->parent()->toArray();
                    if(empty($tempData2)){//新增属性
                        if($canshuVal==''){continue;}
                        $canshuMeta = $tempApi->search('value child')->toArray();
                        if(in_array($canshu,array('notNull','AUTO_INCREMENT','unique'))){
                            $valueType = 'bool';
                        }elseif($canshu=='maxLength'){
                            $valueType = 'int';
                        }elseif($canshu=='default'){
                            if($canshuList['dataType']=='int'){
                                $valueType = 'int';
                            }elseif($canshuList['dataType']=='bool'){
                                $valueType = 'bool';
                            }else{
                                $valueType = 'string';
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
                        if($canshuVal==''){
                            $tempData2[0] = null;
                        }else{
                            if($tempData2[0]['key']['data']=='dataType'){
                                if($canshuList['auto_increment']==true){
                                    $canshuVal = 'int';
                                    $canshuMeta = $tempApi->search('value child')->toArray();
                                    $canshuMeta[0][] = array(
                                        'type'=>'arrayValue',
                                        'key'=>array('type'=>'string','borderStr'=>'\'','data'=>'AUTO_INCREMENT'),
                                        'value'=>array(
                                            'type'=>'bool','data'=>true
                                        ),
                                    );
                                }else{
                                    $temp3 = $tempApi->search('value child key:filter([data=AUTO_INCREMENT])')->parent()->toArray();
                                    if(count($temp3)>0){
                                        $temp3[0] = null;
                                    }
                                }
                            }elseif(in_array($tempData2[0]['key']['data'],array('notNull','unique','primarykey'))){
                                $tempData2[0]['value']['type'] = 'bool';
                            }elseif($tempData2[0]['key']['data']=='maxLength'){
                                $tempData2[0]['value']['type'] = 'int';
                            }elseif($tempData2[0]['key']['data']=='default'){
                                if($canshuList['dataType']=='int'){
                                    $tempData2[0]['value']['type'] = 'int';
                                }elseif($canshuList['dataType']=='bool'){
                                    $tempData2[0]['value']['type'] = 'bool';
                                }else{
                                    $tempData2[0]['value']['type'] = 'string';
                                }
                            }else{
                                $tempData2[0]['value']['type'] = 'string';
                            }
                            $tempData2[0]['value']['data'] = $canshuVal;
                        }
                    }
                }
            }
        }
        //提交git
        if($oldCode!==$phpInterpreter->getCode()){
            $this->pushGit('./admin/'.$thisTableAdminInfo[0]['fileName'],$phpInterpreter->getCode(),'修改了表的后台'.$thisTableAdminInfo[0]['fileName']);
        }
    }
    //保存文件并提交git
    public function pushGit($file,$code,$message){
        $gitAction = new githubClass();
        $gitAction->pull();
        file_put_contents($file,$code);
        $gitAction->add('--all');
        $gitAction->commit($message);
        $gitAction->push();
        $gitAction->branchClean();
    }
    public function __construct()
    {
        $func = $_POST['action'];
        $this->$func();
    }
}
$a = new control();