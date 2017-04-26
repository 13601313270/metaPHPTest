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
            'maxLength' => 20,
            'notNull' => true,
            'title' => 'id',
            'AUTO_INCREMENT' => true,
            'default' => ''
        ),
        'title' => array(
            'dataType' => 'varchar',
            'maxLength' => 255,
            'notNull' => true,
            'title' => '标题',
            'default' => '标题'
        ),
        'img' => array(
            'dataType' => 'imageQiniu',
            'maxLength' => 255,
            'notNull' => true,
            'title' => 'img',
            'default' => ''
        ),
        'ctime' => array(
            'dataType' => 'date',
            'notNull' => true,
            'title' => 'ctime',
            'default' => ''
        ),
        'content' => array(
            'dataType' => 'text',
            'notNull' => true,
            'title' => 'content',
            'default' => ''
        ),
        'type' => array(
            'dataType' => 'int',
            'maxLength' => 11,
            'notNull' => true,
            'title' => 'type',
            'default' => ''
        ),
        'project' => array(
            'dataType' => 'int',
            'maxLength' => 11,
            'notNull' => true,
            'title' => 'project',
            'default' => ''
        ),
        'good' => array(
            'dataType' => 'int',
            'maxLength' => 11,
            'notNull' => true,
            'title' => 'good',
            'default' => '10'
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
