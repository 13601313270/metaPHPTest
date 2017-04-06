<?php /* Smarty version Smarty-3.1.18, created on 2017-04-07 01:59:36
         compiled from "httpAdmin.tpl" */ ?>
<?php /*%%SmartyHeaderCode:142988679158e682080a6ca6-48470383%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'a5479de122dea5bc2fbd7f676ce18049a17a354b' => 
    array (
      0 => 'httpAdmin.tpl',
      1 => 1489996477,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '142988679158e682080a6ca6-48470383',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'fileList' => 0,
    'file' => 0,
    'httpFileConfig' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_58e682080bdd16_74182400',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_58e682080bdd16_74182400')) {function content_58e682080bdd16_74182400($_smarty_tpl) {?><html>
<head>
    <link href="//cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <script src="//cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    
	<link rel="stylesheet" type="text/css" href="http://118.190.95.234/metaPHPTest/cssCreate/httpAdmin.css?1491501576"/>
</head>
<body>
    <section id="fileList">
        <table class="table table-striped">
            <thead></thead>
            <tbody>
                <?php  $_smarty_tpl->tpl_vars['file'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['file']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['fileList']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['file']->key => $_smarty_tpl->tpl_vars['file']->value) {
$_smarty_tpl->tpl_vars['file']->_loop = true;
?>
                    <?php if (in_array($_smarty_tpl->tpl_vars['file']->value,array('.'))) {?><?php continue 1?><?php }?>
                    <tr>
                        <td><?php echo $_smarty_tpl->tpl_vars['file']->value;?>
</td>
                        <td class="fileName"><?php echo $_smarty_tpl->tpl_vars['httpFileConfig']->value[$_smarty_tpl->tpl_vars['file']->value];?>
<span class="btn btn-default">修改</span></td>
                        <td></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </section>
</body>
</html><?php }} ?>
