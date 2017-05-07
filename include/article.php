<?php

/**
* 表article操作接口
*
* User: metaPHP
* Date: 2017/04/25
* Time: 00:37
*/

class article extends kod_db_mysqlSingle{
    protected $tableName = 'article';
    protected $key = 'id';
    protected $keyDataType = 'int';
    public $foreignKey = array(
        'id' => '',
        'title' => '',
        'img' => '',
        'ctime' => '',
        'content' => '',
        'type' => '',
        'project' => '',
        'good' => '',
        'bad' => ''
    );
}

