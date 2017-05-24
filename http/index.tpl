<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no,minimal-ui">
    <link href="//cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <script type="application/javascript" src="//cdn.bootcss.com/jquery/3.2.1/jquery.js"></script>
    <script type="application/javascript" src="//cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>
<section>
    <style>
        body{
        }
    </style>
    {include file="commonModule/head.mod.tpl" title='*' isBeen='4'}
    {include file="commonModule/head.mod.tpl" title='*' isBeen='4'}
    {include file="commonModule/temp.mod.tpl" iId='*'}
    {include file="commonModule/head.mod.tpl" title='标题'}
    {foreach $article as $k=>$v}
        {$k}
    {/foreach}
</section>

</body>
</html>