<?php

include_once("../include.php");
/**
* 表project操作后台
*
* User: metaPHP
* Date: 2017/04/25
* Time: 01:45
*/

class projectAdmin extends kod_web_mysqlAdmin{
    public function getMysqlDbHandle(){
        return new project();
    }
    protected $smartyTpl = 'projectAdmin.tpl';
    protected $dbColumn = array(
        'id' => array(
            'dataType' => 'int',
            'maxLength' => 11,
            'notNull' => true,
            'title' => 'id',
            'AUTO_INCREMENT' => true
        ),
        'projectName' => array(
            'dataType' => 'varchar',
            'maxLength' => 255,
            'notNull' => true,
            'title' => 'projectName'
        ),
        'packName' => array(
            'dataType' => 'varchar',
            'maxLength' => 255,
            'notNull' => true,
            'title' => 'packName'
        ),
        'AppName' => array(
            'dataType' => 'varchar',
            'maxLength' => 255,
            'notNull' => true,
            'title' => 'AppName'
        ),
        'logo' => array(
            'dataType' => 'varchar',
            'maxLength' => 255,
            'notNull' => true,
            'title' => 'logo'
        ),
        'tag' => array(
            'dataType' => 'varchar',
            'maxLength' => 255,
            'notNull' => true,
            'title' => 'tag'
        ),
        'projectType' => array(
            'dataType' => 'int',
            'maxLength' => 11,
            'notNull' => true,
            'title' => 'projectType'
        ),
        'typeInChuiZhiWeb' => array(
            'dataType' => 'varchar',
            'maxLength' => 255,
            'notNull' => true,
            'title' => 'typeInChuiZhiWeb'
        )
    );
    public function main(){
        $adminHtml=$this->getAdminHtml($this->dbColumn);
        $this->assign('adminHtml',$adminHtml);
    }
}

$adminObj=new projectAdmin();
$adminObj->run();
