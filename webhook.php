<?php
/**
 * Created by PhpStorm.
 * User: 王浩然
 * Date: 2017/4/14
 * Time: 下午4:02
 */
$hook_log = json_decode(file_get_contents('php://input'));
print_r($hook_log);