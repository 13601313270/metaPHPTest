<?php

include_once("../include.php");
/**
* 表test2操作后台
*
* User: metaPHP
* Date: 2017/05/02
* Time: 22:28
*/

class test2Admin extends kod_web_mysqlAdmin{
    public function getMysqlDbHandle(){
        return new test2();
    }
    protected $smartyTpl = 'test2Admin.tpl';
    protected $dbColumn = array(
        'mddid' => array(
            'dataType' => 'int',
            'maxLength' => 11,
            'notNull' => true,
            'title' => 'mddid'
        ),
        'name' => array(
            'dataType' => 'varchar',
            'maxLength' => 255,
            'notNull' => true,
            'title' => 'name',
            'unique' => true
        ),
        'title' => array(
            'dataType' => 'varchar',
            'maxLength' => 255,
            'notNull' => true,
            'title' => 'title',
            'unique' => true
        ),
        'ddd' => array(,
            'dataType' => 'int',
            'title' => '巴拉',
            'primarykey' => true,
            'name' => 'ddd'
        )
    );
    public function main(){
        $adminHtml=$this->getAdminHtml($this->dbColumn);
        $this->assign('adminHtml',$adminHtml);
    }
}

$adminObj=new test2Admin();
$adminObj->run();
