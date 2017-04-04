<?php
/**
 * Created by PhpStorm.
 * User: 王浩然
 * Date: 2017/3/20
 * Time: 下午2:14
 */
include_once('include.php');

$page = new kod_web_page();
$page->fileList = scandir('./http/');
$page->httpFileConfig = array(
    'index.php'=>'index',
);
//$page->fetch('httpAdmin.tpl');

$searchBase = array(
    'type'=>'window',
    'child'=>array(
        array(
            'type'=>'if',
            'value'=>array('type'=>'bool','data'=>'true'),
            'child'=>array(
                array(
                    'type'=>'=',
                    'object1'=>array(
                        'type'=>'objectParams',
                        'object'=>array(
                            'type'=>'variable',
                            'name'=>'$page',
                        ),
                        'name'=>'fileList',
                    ),
                    'object2'=>array(
                        'type'=>'new',
                        'className'=>'metaSearch',
                        'property'=>array(
                            array(
                                'type'=>'functionCall',
                                'name'=>'file',
                                'property'=>array('__FILE__'),
                            ),
                        ),

                    ),
                ),
                array(
                    'type'=>'functionCall',
                    'name'=>'include_once',
                    'property'=>array(
                        array(
                            'type'=>'string',
                            'borderStr'=>"'",
                            'data'=>'include.php'
                        ),
                    ),
                ),
                array(
                    'type'=>'=',
                    'object1'=>array(
                        'type'=>'objectParams',
                        'object'=>array(
                            'type'=>'variable',
                            'name'=>'$page',
                        ),
                        'name'=>'fileList',
                    ),
                    'object2'=>array(
                        'type'=>'new',
                        'className'=>'phpInterpreter',
                        'property'=>array(
                            array(
                                'type'=>'functionCall',
                                'name'=>'file',
                                'property'=>array('__FILE__'),
                            ),
                        ),

                    ),
                ),
            ),
        ),
    ),
);
$searchApi = new metaSearch($searchBase);

//字符.,查找的是type等于某个值的子元素.=就代表 'type'=>'=' ,的元素
//print_r($searchApi->search($searchBase,'.='));

//字符#,查找的是name等于某个值的子元素 #fileList 就代表'name'=>'fileList',的元素
//$re = $searchApi->search('#fileList')->toArray();
$re = $searchApi->search('.= .new')->parent()->parent()->toArray();
print_r($re);exit;

print_r($searchBase);

//用中括号包裹的含义,代表查找包含某个属性的元素,[object2]就代表包含object2属性的元素
//print_r($searchApi->search('[object2]'));

//用中括号包裹,并且内部有等于判断,代表查找包含某个属性的元素,并且元素的值等于=后面,[className=phpInterpreter]就代表包含className属性,并且值是phpInterpreter的元素
//print_r($searchApi->search('[className=phpInterpreter]'));

//当然也可以复合使用,会类似jquery从父层往下查去匹配
//print_r($searchApi->search('.= .new'));//查找type='='里的type='new'

//直接输入字符查找,下面例子中的object2,带包直接进入object2作为命中
//print_r($searchApi->search('.= object2'));//查找type='='里的object2

//filter过滤器,对查出来的结果进行过滤.判断是否满足filter()括号里包含的特征
//print_r($searchApi->search('.= object2:filter([className=phpInterpreter])'));

exit;



////$find = $pageMeta->search('.= .new:filter([className=kod_web_page])');
////$find = $pageMeta->search('.=');
////print_r($find);exit;
//print_r($pageMeta->codeMeta);
exit;
print_r($pageMeta->codeMeta);
