<?php

/**
 * Created by PhpStorm.
 * User: 王浩然
 * Date: 2017/3/20
 * Time: 下午2:14
 */

include_once('include.php');
include_once('kod/smarty/libs/Smarty.class.php');
class compiler extends Smarty_Internal_SmartyTemplateCompiler{
    public function doCompile($_content, $isTemplateSource = false)
    {
        return parent::doCompile($_content, $isTemplateSource); // TODO: Change the autogenerated stub
    }
}
class control{
    public function __construct()
    {
        $func = $_POST['action'];
        if($func!=='getSessionState'){
            session_start();
            if($_SESSION['program']['program']<100 && $_SESSION['program']['program']>0){
                echo json_encode(array(
                    'return'=>false,
                    'text'=>'有操作正在运算中,请稍后再试',
                ));exit;
            }
        }
        if(method_exists($this,$func)){
            $this->$func();
        }else{
            $this->main();
        }
    }
    public function main(){
        $page=new kod_web_page();
        if(in_array($_GET['file'],scandir('./http/'))){
            $page->file = $_GET['file'];

            $metaApi = new phpInterpreter(file_get_contents('./http/'.$_GET['file']));
            $PageObj = $metaApi->search('.= [className=kod_web_page]')->parent()->toArray();
            $PageObj = $PageObj[0]['object1']['name'];

            //用到的tpl文件
            $tplFile = $metaApi->search('.objectFunction:filter(#fetch) object:filter([name='.$PageObj.'])')->parent()->toArray();
            $tplFile = $tplFile[0]['property'][0]['data'];
            $page->tplFile = $tplFile;

            //加载所有通用模块
            $allModule = array();
            foreach(scandir('./http/commonModule/') as $name){
                if( substr($name,-8)=='.mod.tpl' ){
                    $itemModNam = 'commonModule/'.substr($name,0,-8);
                    $callArgs = array();
                    if(file_exists(webDIR.$itemModNam.'.mod.php')){
                        include_once(webDIR.$itemModNam.'.mod.php');
                        $className = 'kodMod_'.implode('_',explode('/',$itemModNam));
                        $modController = new $className();
                        if(method_exists($modController,'init')){
                            $method = new ReflectionMethod($className, 'init');
                            foreach($method->getParameters() as $v){
                                $argsName = $v->getName();

                                if($v->isOptional()){
                                    $callArgs[] = array(
                                        'name'=>$argsName,
                                        'default'=>$v->getDefaultValue()
                                    );
                                }else{
                                    $callArgs[] = array(
                                        'name'=>$argsName,
                                    );
                                }
                            }
                        }
                    }
                    $allModule[] = array(
                        'name'=>$itemModNam,
                        'html'=>file_get_contents('./http/commonModule/'.$name),
                        'callArgs'=>$callArgs
                    );
                }
            }
            $page->allModule = $allModule;
            //加载所有通用模板
            $allTemplage = array();
            foreach(scandir('./http/template/') as $name){
                if( substr($name,-11)=='.layout.tpl' ){
                    $itemModNam = 'template/'.substr($name,0,-11);
                    $callArgs = array();
                    if(file_exists(webDIR.$itemModNam.'.layout.php')){
                        include_once(webDIR.$itemModNam.'.layout.php');
                        $className = 'kodTmp_'.implode('_',explode('/',$itemModNam));
                        $layoutController = new $className();
                        if(method_exists($layoutController,'init')){
                            $method = new ReflectionMethod($className, 'init');
                            foreach($method->getParameters() as $v){
                                $argsName = $v->getName();
                                if($v->isOptional()){
                                    $callArgs[] = array(
                                        'name'=>$argsName,
                                        'default'=>$v->getDefaultValue()
                                    );
                                }else{
                                    $callArgs[] = array(
                                        'name'=>$argsName,
                                    );
                                }
                            }
                        }
                    }
                    $allTemplage[] = array(
                        'name'=>$itemModNam,
                        'callArgs'=>$callArgs,
                        'tplContent'=>file_get_contents('./http/template/'.$name)
                    );
                }
            }
            $page->allTemplage = $allTemplage;
            //使用的GET参数
            $allGet = $metaApi->search('.arrayGet object:filter([name=$_GET])')->parent()->toArray();
            $allKeys = array();
            foreach($allGet as $v){
                if(!in_array($v['key']['data'],$allKeys)){
                    $allKeys[] = $v['key']['data'];
                }
            }
            $page->allGet = $allKeys;
            $page->tplFileContent = file_get_contents('./http/'.$page->tplFile);
            $page->phpFileContent = file_get_contents('./http/'.$page->file);
            $page->fetch('pageEditAdmin.tpl');
        }
    }
    public static function debug($data,$type){
        if($type==''){

        }else{
            ob_clean();
            echo json_encode(array(
                'debug'=>true,
                'type'=>$type,
                'data'=>$data
            ));exit;
        }
    }
    public function runData(){
        $phpLine = $_POST['phpLine'];
        $phpContent = $_POST['phpContent'];
        //如果对php文件进行了修改,则尝试进行语法提示
        if($_POST['onEditor']=='php'){
            $writePosition = -1;
            for($i=0 ; $i<$phpLine['row'] ; $i++){
                $writePosition = mb_strpos($phpContent,"\n",$writePosition+1);
            }
            $writePosition += $phpLine['column']+1;
            $runTimeWrite = mb_substr($phpContent,0,$writePosition);
            //获取变量属性
            if(preg_match('/\$$/',$runTimeWrite,$match)){
                $runTimeWrite = preg_replace('/\$$/','#debug(variable,callStack)#',$runTimeWrite);
            }elseif(preg_match('/->$/',$runTimeWrite,$match)){
                $runTimeWrite = preg_replace('/->$/','->#debug(variable,callStack)#',$runTimeWrite);
            }elseif(preg_match('/\[$/',$runTimeWrite,$match)){
                $runTimeWrite = preg_replace('/\[$/','[#debug(variable,callStack)#',$runTimeWrite);
            }
            $_content = $runTimeWrite;
            $metaApi = new phpInterpreter($_content);
            $evalObj = new evalMetaCode($metaApi->codeMeta,array(
                '$_GET'=>$_POST['simulate']
            ));
            $pushResult = $evalObj->run();
            if(isset($pushResult['debug'])){
                ob_clean();
                echo json_encode(array(
                    'debug'=>true,
                    'type'=>'objectParams',
                    'data'=>$pushResult['debug']['variable']
                ));exit;
            }
        }
        $metaApi = new phpInterpreter($phpContent);
        $PageObj = $metaApi->search('.= [className=kod_web_page]')->parent()->toArray();
        $PageObj = $PageObj[0]['object1']['name'];
        $tplLine = $_POST['tplLine'];
        //执行脚本,计算出所有推送到前端的变量
        if($_POST['simulate']){
            $tplFile = $metaApi->search('.objectFunction:filter(#fetch) object:filter([name='.$PageObj.'])')->parent()->toArray();//删除fetch输出调用
            $evalObj = new evalMetaCode(array(),array('$_GET'=>$_POST['simulate']));
            $template = $evalObj->base($tplFile[0]['property'][0]);
            $tplFile[0] = array(
                'type'=>'returnEvalValue',
                'key'=>array(
                    'type'=>'string',
                    'data'=>'pushValue'
                ),
                'value'=>$tplFile[0]['object'],
            );
            $evalObj = new evalMetaCode($metaApi->codeMeta,array(
                '$_GET'=>$_POST['simulate']
            ));
            $pushResult = $evalObj->run();
            $pushResult_ = array();
            foreach($pushResult['pushValue'] as $k=>$v){
                $pushResult_[$k] = $v;
            }
            $pushResult = $pushResult_;
        }else{
            $pushResult = array();
        }
        $smartyObject = new kod_web_smarty();
        $smartyObject->setTemplateDir('http');//设置模板目录
        //kod_web_smarty_internal_template
        $template = new $smartyObject->template_class($template, $smartyObject, null, null, null, null, null);

        $template->parent = $smartyObject;
        if (!empty($pushResult) && is_array($pushResult)) {
            foreach ($pushResult as $k => $v) {
                $template->tpl_vars[ $k ] = new Smarty_Variable($v);
            }
        }

        if (!empty(Smarty::$global_tpl_vars)) {
            $template->tpl_vars = array_merge(Smarty::$global_tpl_vars, $template->tpl_vars);
        }

        $compiler = new compiler($template->source->template_lexer_class, $template->source->template_parser_class, $template->smarty);
        $compiler->template = $template;
        $compiler->php_handling = $template->smarty->php_handling;
        $compiler->parent_compiler = $compiler;
        $compiler->nocache_hash = $template->compiled->nocache_hash;
        $template->compiled->has_nocache_code = false;
        if ($compiler->smarty->merge_compiled_includes || $template->source->handler->checkTimestamps()) {
            $compiler->parent_compiler->template->compiled->file_dependency[ $template->source->uid ] =
                array($template->source->filepath,
                    $template->source->getTimeStamp(),
                    $template->source->type,);
        }
        $compiler->smarty->_current_file = $template->source->filepath;

        $_content = $_POST['tplContent'];

        //如果对tpl文件进行了修改,则尝试进行语法提示
        if($_POST['onEditor']=='tpl'){
            //找到用户tpl文件输入点
            $writePosition = -1;
            for($i=0 ; $i<$tplLine['row'] ; $i++){
                $writePosition = mb_strpos($_content,"\n",$writePosition+1);
            }
            $writePosition += $tplLine['column']+1;
            $runTimeWrite = mb_substr($_content,0,$writePosition);
            //获取变量属性
            if(preg_match('/(\$[A-z|_][A-z|_|0-9]*)\.$/',$runTimeWrite,$match)){
                $runTimeWrite = preg_replace('/(\$[A-z|_][A-z|_|0-9]*)\.$/','control::debug($1,\'objectParams\')',$runTimeWrite);
                $_content = $runTimeWrite.mb_substr($_content,$writePosition);
            }
        }



        //添加一个html注释,好让前端程序知道某一段html是通过模块可以模板修改的
        $_content = preg_replace('/({block name=(\S+)})/','$1<!--blockBegin name($2)-->',$_content);
        $_content = preg_replace('/({\/block})/','<!--blockEnd-->$1',$_content);

        $_content = $compiler->preFilter($_content);
        $_content = $compiler->doCompile($_content, true);
        $_content = $compiler->postFilter($_content);
        $_content = $compiler->blockOrFunctionCode.$_content;

        $compiler->parent_compiler = null;
        $compiler->parser = null;

        //添加一个html注释,好让前端程序知道某一段html是通过模块include添加的
        $_content = preg_replace('/(<\?php \$_smarty_tpl->_subTemplateRender\("file:(\S+?)")/','<!--useMod $2-->$1',$_content);
        //开始执行生成的php代码
        $metaApi = new phpInterpreter($_content);
        $runApi = new evalMetaCode($metaApi->codeMeta,array(
            '$_smarty_tpl'=>$template
        ));
        ob_start();
        $runApi->run();
        $template->_cleanUp();
        $string = ob_get_contents();
        ob_clean();
//        $pushResult['SCRIPT_NAME'] = $template->tpl_vars['SCRIPT_NAME']->value;
        echo json_encode(array(
            'pushResult'=>$pushResult,
            'html'=>$string,
        ));exit;
    }
    public function save(){
        $metaApi = new phpInterpreter(file_get_contents('./http/'.$_POST['file']));
        $PageObj = $metaApi->search('.= [className=kod_web_page]')->parent()->toArray();
        $PageObj = $PageObj[0]['object1']['name'];
        $tplFile = $metaApi->search('.objectFunction:filter(#fetch) object:filter([name='.$PageObj.'])')->parent()->toArray();//删除fetch输出调用

        $runApi = new evalMetaCode(array(
            'type'=>'returnEvalValue',
            'key'=>array('type'=>'string','data'=>'tplFile'),
            'value'=>$tplFile[0]['property'][0],
        ),array());
        $result = $runApi->run();
        $tplFile = $result['tplFile'];
        try{
            $result = file_put_contents('./http/'.$tplFile,$_POST['tplContent'])
                && file_put_contents('./http/'.$_POST['file'],$_POST['phpContent']);
            if($result){
                echo json_encode(array('result'=>true));
            }else{
                echo json_encode(array('result'=>false));
            }
        }catch (Exception $e){
            echo json_encode(array('result'=>false,'message'=>$e->getMessage()));
        }
    }
    public function saveImg(){
        $file = $_POST['file'];
        $base64 = $_POST['content'];
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64, $result)){
            $new_file = "./metaPHPCacheFile/".$file;
            $dirname = dirname($new_file);
            if (!is_dir($dirname)){
                mkdir($dirname);
            }
            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64)))){
                echo true;
            }
        }
        echo false;
    }
}
$a = new control();