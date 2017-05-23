
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
$smartyObject = new kod_smarty_smarty();
$smartyObject->compile_dir = KOD_SMARTY_COMPILR_DIR;//设置编译目录
foreach($page as $k=>$v){
    $smartyObject->assign($k,$v);
}
//$page->fetch('index.tpl');exit;


$template = 'index.tpl';
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
        $_content = $compiler->template->source->content;
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
echo $_compiled_code;//获取生成的php代码

//echo $_template->getSubTemplate ("temp.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);
//exit;



//var_dump(array("temp.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0));exit;
$phpInterpreterApi = new phpInterpreter($_compiled_code);

//print_r($phpInterpreterApi->codeMeta);
$evalMetaCodeApi = new evalMetaCode($phpInterpreterApi->codeMeta,array(
    '$_smarty_tpl'=>$_template
));
$evalMetaCodeApi->run();exit;