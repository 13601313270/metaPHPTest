<?php

include_once("../include.php");
/**
* 表article操作后台
*
* User: metaPHP
* Date: 2017/04/25
* Time: 01:01
*/

class articleAdmin extends kod_web_mysqlAdmin{
    public function getMysqlDbHandle(){
        return new article();
    }
    protected $smartyTpl = 'articleAdmin.tpl';
    protected $dbColumn = array(
        'id' => array(
            'dataType' => 'bigint',
            'maxLength' => ,
            'notNull' => true,
            'title' => 'id',
            'AUTO_INCREMENT' => false
        ),
        'title' => array(
            'dataType' => 'varchar',
            'maxLength' => 512,
            'notNull' => true,
            'title' => '标题'
        ),
        'img' => array(
            'dataType' => 'imageQiniu',
            'maxLength' => ,
            'notNull' => true,
            'title' => 'img'
        ),
        'ctime' => array(
            'dataType' => 'date',
            'notNull' => true,
            'title' => 'ctime'
        ),
        'content' => array(
            'dataType' => 'text',
            'notNull' => true,
            'title' => 'content'
        ),
        'type' => array(
            'dataType' => 'int',
            'maxLength' => ,
            'notNull' => true,
            'title' => 'type'
        ),
        'project' => array(
            'dataType' => 'int',
            'maxLength' => ,
            'notNull' => true,
            'title' => 'project'
        ),
        'good' => array(
            'dataType' => 'int',
            'maxLength' => ,
            'notNull' => true,
            'title' => 'good'
        ),
        'bad' => array(
            'dataType' => 'int',
            'maxLength' => ,
            'notNull' => true,
            'title' => 'bad'
        )
    );
    public function main(){
        $adminHtml=$this->getAdminHtml($this->dbColumn);
        $this->assign('adminHtml',$adminHtml);
    }
}

$adminObj=new articleAdmin();
$adminObj->run();
