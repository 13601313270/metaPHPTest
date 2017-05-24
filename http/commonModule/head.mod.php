<?php
/**
 *
 *
 * User: 王浩然
 * Date: 2017/04/25
 * Time: 00:37
 */
class kodMod_commonModule_head extends kod_web_smartyModController{
    public function init($title,$isBeen=4){
        $this->assign('titleName',$title);
    }
    public function finish($aData)
    {
    }
}