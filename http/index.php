<?php
/**
 * Created by PhpStorm.
 * User: 王浩然
 * Date: 2017/3/20
 * Time: 下午2:08
 */
include_once('../include.php');
$page=new kod_web_page();

$a = array('s'=>'ddd');
$b = $a['s'];
$c = new article();
$c->foreignKey = array();
$page->id = $_GET['id'];
$page->chid = $_GET['chid'];
$page->article = article::create()->getByKey($_GET['id']);

$page->title = '标题';
$page->fetch('index.tpl');