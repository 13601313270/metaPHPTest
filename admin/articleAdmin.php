<?php

/**
* 表article操作后台
*
* User: metaPHP
* Date: 2017/04/23
* Time: 21:32
*/

class articleAdmin extends kod_web_mysqlAdmin{
    public function getMysqlDbHandle(){
        return new article();
    }
    protected $smartyTpl = 'article.tpl';
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
}

$adminObj=new articleAdmin();
$adminObj->run();
