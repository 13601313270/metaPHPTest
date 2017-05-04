<?php

include_once("../include.php");
/**
* 表article操作后台
*
* User: metaPHP
* Date: 2017/05/05
* Time: 03:38
*/

class articleAdmin extends kod_web_mysqlAdmin{
    public function getMysqlDbHandle(){
        return new article();
    }
    protected $smartyTpl = 'articleAdmin.tpl';
    protected $dbColumn = array(
        'id' => array(
            'dataType' => 'int',
            'maxLength' => 11,
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
            'dataType' => 'datetime',
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
            'notNull' => true,
            'title' => 'good',
            'default' => '0'
        ),
        'bad' => array(
            'dataType' => 'int',
            'maxLength' => 11,
            'notNull' => true,
            'title' => 'bad',
            'default' => '0'
        )
    );
    public function main(){
        $adminHtml=$this->getAdminHtml($this->dbColumn);
        $this->assign('adminHtml',$adminHtml);
    }
}

$adminObj=new articleAdmin();
$adminObj->run();
