<?php
/**
 *
 *
 * User: 王浩然
 * Date: 2017/04/25
 * Time: 00:37
 */
class kodMod_commonModule_head extends kod_web_smartyModController{
    public function init($type){
        if($type==1){
            $this->assign('headTitle','我是第一种类型');
        }elseif($type==2){
            $this->assign('headTitle','我是第二种类型');
        }else{
            $this->assign('headTitle','我是其他类型');
        }
    }
    public function finish()
    {
    }
}