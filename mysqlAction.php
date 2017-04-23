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
if($_POST['action']=='tables'){
    $api = new kod_db_mysqlDB();
    $data = kod_db_mysqlDB::create(KOD_COMMENT_MYSQLDB)->runsql('show table status');
    foreach($data as $k=>$v){
        $data[$k]['database'] = KOD_COMMENT_MYSQLDB;
    }
    echo json_encode($data);
}elseif($_POST['action']=='showTableColumn'){
    $allTable = kod_db_mysqlDB::create()->runsql('show tables');
    $allTable = kod_tool_array::getNewArrOfArrColumn($allTable,'Tables_in_phoneGap');
    $className = $_POST['name'];
    if(in_array($className,$allTable)){
        $tableInfo = current(kod_db_mysqlDB::create()->runsql('show create table '.$className));
        if(preg_match('/CREATE TABLE ".+?"\s*\(([\S|\s]*)\)$/',$tableInfo['Create Table'],$match)){
            $tableInfo = explode(',',$match[1]);
            $primaryKey = '';//主键
            $option = array();
            foreach($tableInfo as $k=>$v){
                if(preg_match("/[`|\"](\S+)[`|\"] (int|smallint|varchar|tinyint|char|bigint)\((\d+)\)( NOT NULL| DEFAULT NULL)?( DEFAULT '(\S+)'| AUTO_INCREMENT)?( COMMENT '(\S+)')?/",$v,$match)){
                    $option[$match[1]] = array(
                        "dataType"=>$match[2],
                        "maxLength"=>$match[3],
                        "notNull"=>!empty($match[4]),
                        "title"=>empty($match[8])?$match[1]:$match[8],
                    );
                    if(!empty($match[5]) && $match[5]==" AUTO_INCREMENT"){
                        $primaryKey = $match[1];
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
                    $primaryKey = $match[1];
                }
            }
//            print_r($option);
            //现有接口类
            $fileList = scandir('./include/');
            if(count($fileList)==2){
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
                $newClass->setProperty('key',array('type'=>'string','borderStr'=>"'",'data'=>$primaryKey), 'protected');
                $newClass->setProperty('keyDataType',array('type'=>'string','borderStr'=>"'",'data'=>$option[$primaryKey]['dataType']), 'protected');

                $gitAction = new githubClass();
                $gitAction->pull();
                file_put_contents('./include/'.$className.'.php',$newClass->phpInterpreter->getCode());
                $gitAction->add('--all');
                $gitAction->commit('增加了表'.$className.'的操作接口类');
                $gitAction->push();
                $gitAction->branchClean();
            }

//            $headCommit = $gitAction->exec('git rev-parse HEAD');
//
//            $fileList = scandir('./include/');
//            print_r($fileList);exit;
//
//
//            $allIncludeApi = kod_db_memcache::returnCacheOrSave('allIncludeApi',function(){
//
//            },0,0,function($data)use($headCommit){
//                return $data['headCommit']==$headCommit;
//            });




            var_dump($headCommit[0]);exit;
//            $allIncludeApi = kod_db_memcache::returnCacheOrSave('')
            print_r($option);
//            final class temp extends kod_db_mysqlSingle{
//                protected $tableName = 'baiduBack';
//                protected $key = 'keyWord';
//                protected $keyDataType = 'varchar';
//            }
//            class a extends kod_web_mysqlAdmin{
//                public function getMysqlDbHandle(){
//                    return new temp();
//                }
//                protected $smartyTpl = "baiduBack.tpl";
//                protected $dbColumn = array();
//                public function main(){
//                    $adminHtml = $this->getAdminHtml($this->dbColumn);
//                    $this->assign("adminHtml",$adminHtml);
//                }
//            }
//            $abc = new a();
//            $abc->run();
//            print_r($option);exit;
        }
    }
}