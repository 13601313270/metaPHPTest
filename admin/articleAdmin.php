<?php

include_once("../include.php");
/**
* 表article操作后台
*
* User: metaPHP
* Date: 2017/04/23
* Time: 22:04
*/

class articleAdmin extends kod_web_mysqlAdmin{
    public function getMysqlDbHandle(){
        return new article();
    }
    protected $smartyTpl = 'articleAdmin.tpl';
    protected $dbColumn = array(
        'id' => array(
            'dataType' => 'bigint',
            'maxLength' => 20,
            'notNull' => true,
            'title' => 'id',
            'AUTO_INCREMENT' => true
        ),
        'title' => array(
            'dataType' => 'varchar',
            'maxLength' => 255,
            'notNull' => true,
            'title' => 'title'
        ),
        'img' => array(
            'dataType' => 'varchar',
            'maxLength' => 255,
            'notNull' => true,
            'title' => 'img'
        ),
        'ctime' => array(
            'dataType' => 'date',
            'notNull' => false,
            'title' => 'ctime'
        ),
        'content' => array(
            'dataType' => 'text',
            'notNull' => true,
            'title' => 'content'
        ),
        'type' => array(
            'dataType' => 'int',
            'maxLength' => 11,
            'notNull' => true,
            'title' => 'type'
        ),
        'project' => array(
            'dataType' => 'int',
            'maxLength' => 11,
            'notNull' => true,
            'title' => 'project'
        ),
        'good' => array(
            'dataType' => 'int',
            'maxLength' => 11,
            'notNull' => false,
            'title' => 'good'
        ),
        'bad' => array(
            'dataType' => 'int',
            'maxLength' => 11,
            'notNull' => false,
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
