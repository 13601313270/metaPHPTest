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
            'notNull' => "true",
            'title' => 'id',
            'AUTO_INCREMENT' => true,
            'default' => ''
        ),
        'title' => array(
            'dataType' => 'varchar',
            'maxLength' => "255",
            'notNull' => "true",
            'title' => 'title',
            'default' => ''
        ),
        'orderNum' => array(
            'dataType' => 'int',
            'maxLength' => 11,
            'notNull' => "true",
            'title' => 'orderNum',
            'default' => ''
        ),
        'projectId' => array(
            'dataType' => 'int',
            'maxLength' => 11,
            'notNull' => "true",
            'title' => 'projectId',
            'default' => ''
        ),
        'icon' => array(
            'dataType' => 'varchar',
            'maxLength' => "255",
            'notNull' => "true",
            'title' => 'icon',
            'default' => ''
        ),
        'keyWord' => array(
            'dataType' => 'varchar',
            'maxLength' => "255",
            'notNull' => "true",
            'title' => 'keyWord',
            'default' => ''
        )
    );
}

$adminObj=new articleTypeAdmin();
$adminObj->run();
