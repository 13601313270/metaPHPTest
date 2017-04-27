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
            'AUTO_INCREMENT' => true
        ),
        'title' => array(
            'dataType' => 'varchar',
            'maxLength' => 255,
            'notNull' => true,
            'title' => '标题',
            'default' => 新闻
        ),
        'img' => array(
            'dataType' => 'imageQiniu',
            'maxLength' => 255,
            'notNull' => true,
            'title' => '列表图'
        ),
        'ctime' => array(
            'dataType' => 'datetime',
            'notNull' => true,
            'title' => '创建时间'
        ),
        'content' => array(
            'dataType' => 'text',
            'notNull' => true,
            'title' => '正文'
        ),
        'type' => array(
            'dataType' => 'int',
            'maxLength' => 11,
            'notNull' => true,
            'title' => '分类'
        ),
        'project' => array(
            'dataType' => 'int',
            'maxLength' => 11,
            'notNull' => true,
            'title' => '项目'
        ),
        'good' => array(
            'dataType' => 'int',
            'maxLength' => 11,
            'notNull' => true,
            'title' => '点赞',
            'default' => 0
        ),
        'bad' => array(
            'dataType' => 'int',
            'maxLength' => 11,
            'notNull' => true,
            'title' => '差评',
            'default' => 0
        )
    );
    public function main(){
        $adminHtml=$this->getAdminHtml($this->dbColumn);
        $this->assign('adminHtml',$adminHtml);
    }
}

$adminObj=new articleAdmin();
$adminObj->run();
