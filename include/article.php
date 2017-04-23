<?php

/**
* 表article操作接口
*
* User: metaPHP
* Date: 2017/04/23
* Time: 17:10
*/

class article extends kod_db_mysqlSingle{
    protected $tableName = 'article';
    protected $key = 'id';
    protected $keyDataType = 'bigint';
}

