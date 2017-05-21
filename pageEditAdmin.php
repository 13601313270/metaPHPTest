<?php

/**
 * Created by PhpStorm.
 * User: 王浩然
 * Date: 2017/3/20
 * Time: 下午2:14
 */

include_once('include.php');
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
                    $allModule[] = substr($name,0,-8);
                }
            }
            $page->allModule = $allModule;

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
            $page->fetch('pageEditAdmin.tpl');
        }
    }
    public function runData(){
        $metaApi = new phpInterpreter(file_get_contents('./http/'.$_POST['file']));
        $PageObj = $metaApi->search('.= [className=kod_web_page]')->parent()->toArray();
        $PageObj = $PageObj[0]['object1']['name'];
        $line = $_POST['line'];
//        print_r($line);exit;

        //执行脚本,计算出所有推送到前端的变量
        if($_POST['simulate']){
            $allPushParams = $metaApi->search('.objectParams object:filter(#'.$PageObj.')')->parent()->parent()->toArray();
            foreach($allPushParams as $k=>$v){
                $allPushParams[$k] = array(
                    'type'=>'returnEvalValue',
                    'key'=>array(
                        'type'=>'string',
                        'data'=>$v['object1']['name']
                    ),
                    'value'=>$v['object2'],
                );
            }
            $tplFile = $metaApi->search('.objectFunction:filter(#fetch) object:filter([name='.$PageObj.'])')->parent()->toArray();//删除fetch输出调用
            $evalObj = new evalMetaCode(array(),array('$_GET'=>$_POST['simulate']));
            $template = $evalObj->base($tplFile[0]['property'][0]);
            $tplFile[0] = null;
            $evalObj = new evalMetaCode($metaApi->codeMeta,array(
                '$_GET'=>$_POST['simulate']
            ));
            $pushResult = $evalObj->run();
        }else{
            $pushResult = array();
        }
        $smartyObject = new kod_smarty_smarty();
        $smartyObject->compile_dir = KOD_SMARTY_COMPILR_DIR;//设置编译目录
        foreach($pushResult as $k=>$v){
            $smartyObject->assign($k,$v);
        }

        $_template = new Smarty_Internal_Template($template, clone $smartyObject->smarty, $smartyObject, null, null);
        $ptr_array = array($_template->parent,$_template);

        //一层层获取变量
        $parent_ptr = $_template->parent;

        $tpl_vars = $parent_ptr->tpl_vars;
        $config_vars = $parent_ptr->config_vars;
        while ($parent_ptr = next($ptr_array)) {
            if (!empty($parent_ptr->tpl_vars)) {
                $tpl_vars = array_merge($tpl_vars, $parent_ptr->tpl_vars);
            }
            if (!empty($parent_ptr->config_vars)) {
                $config_vars = array_merge($config_vars, $parent_ptr->config_vars);
            }
        }
        if (!empty(Smarty::$global_tpl_vars)) {
            $tpl_vars = array_merge(Smarty::$global_tpl_vars, $tpl_vars);
        }
        $_template->tpl_vars = $tpl_vars;

        $_template->config_vars = $config_vars;
        $_template->tpl_vars['smarty'] = new Smarty_Variable;//虚拟本地smarty变量 dummy local smarty variable
        $_template->smarty->merged_templates_func = array();// must reset merge template date

        $_template->properties['file_dependency'] = array();
        $_template->properties['file_dependency'][$_template->source->uid] = array($_template->source->filepath, $_template->source->timestamp, $_template->source->type);

        $compiler = new Smarty_Internal_SmartyTemplateCompiler('Smarty_Internal_Templatelexer','Smarty_Internal_Templateparser',$_template->smarty);

// flag for nochache sections
        $compiler->nocache = false;
        $compiler->tag_nocache = false;
        $compiler->template = $_template;
        $compiler->template->has_nocache_code = false;
        if (empty($compiler->template->source->components)) {
            $compiler->sources = array($_template->source);
        } else {
            $compiler->sources = array_reverse($_template->source->components);
        }
        $loop = 0;
        while ($compiler->template->source = array_shift($compiler->sources)) {
            $compiler->smarty->_current_file = $compiler->template->source->filepath;
            if ($compiler->smarty->debugging) {
                Smarty_Internal_Debug::start_compile($compiler->template);
            }
            $no_sources = count($compiler->sources);
            if ($loop || $no_sources) {
                $compiler->template->properties['file_dependency'][$compiler->template->source->uid] = array($compiler->template->source->filepath, $compiler->template->source->timestamp, $compiler->template->source->type);
            }
            $loop++;
            if ($no_sources) {
                $compiler->inheritance_child = true;
            } else {
                $compiler->inheritance_child = false;
            }
            do {
                $_compiled_code = '';
                $compiler->abort_and_recompile = false;
                if($compiler->template->source->filepath==webDIR.'index.tpl'){
                    $_content = $_POST['content'];
                }else{
                    $_content = $compiler->template->source->content;
                }
                if ($_content != '') {
                    if ((isset($compiler->smarty->autoload_filters['pre']) || isset($compiler->smarty->registered_filters['pre'])) && !$compiler->suppressFilter) {
                        $_content = Smarty_Internal_Filter_Handler::runFilter('pre', $_content, $_template);
                    }
                    $_compiled_code = $compiler->doCompile($_content);// 把tpl模板的内容转换为原生的php内容
                }
            } while ($compiler->abort_and_recompile);
            if ($compiler->smarty->debugging) {
                Smarty_Internal_Debug::end_compile($compiler->template);
            }
        }
        $_compiled_code = Smarty_Internal_Filter_Handler::runFilter('post', $_compiled_code, $_template);
//        echo $_compiled_code;//获取生成的php代码
        $phpInterpreterApi = new phpInterpreter($_compiled_code);
        $evalMetaCodeApi = new evalMetaCode($phpInterpreterApi->codeMeta,array(
            '$_smarty_tpl'=>$_template
        ));
        ob_start();
        $evalMetaCodeApi->run();
        $string = ob_get_contents();
        ob_clean();
        echo $string;exit;
        echo json_encode(array(
            'pushResult'=>$tpl_vars,
            'html'=>$string,
        ));
        exit;
    }
}
$a = new control();