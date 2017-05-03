<?php

/**
* 表articleType操作后台
*
* User: metaPHP
* Date: 2017/04/23
* Time: 21:01
*/

class articleTypeAdmin extends kod_web_mysqlAdmin{
    public function getMysqlDbHandle(){
        return new articleType();
    }
    protected $smartyTpl = 'articleType.tpl';
    protected $dbColumn = array(
        'id' => array(
            'dataType' => 'int',
            'maxLength' => 11,
            'notNull' => true,
            'title' => 'id',
            'AUTO_INCREMENT' => true,
            'default' => '',
            'AUTO_INCREMENT' => true,
            'auto_increment' => '1',
            'AUTO_INCREMENT' => true
        ),
        'title' => array(
            'dataType' => 'varchar',
            'maxLength' => 255,
            'notNull' => true,
            'title' => '标题',
            'default' => ''
        ),
        'orderNum' => array(
            'dataType' => 'int',
            'maxLength' => 11,
            'notNull' => true,
            'title' => '排序',
            'default' => ''
        ),
        'projectId' => array(
            'dataType' => 'int',
            'maxLength' => 11,
            'notNull' => true,
            'title' => '项目',
            'default' => ''
        ),
        'icon' => array(
            'dataType' => 'varchar',
            'maxLength' => 255,
            'notNull' => true,
            'title' => '图标',
            'default' => ''
        ),
        'keyWord' => array(
            'dataType' => 'varchar',
            'maxLength' => 255,
            'notNull' => true,
            'title' => '关键词',
            'default' => ''
        )
    );
}

$adminObj=new articleTypeAdmin();
$adminObj->run();
