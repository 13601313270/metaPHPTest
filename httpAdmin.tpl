<html>
<head>
    <link href="//cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <script src="//cdn.bootcss.com/jquery/3.2.1/jquery.js"></script>
    <script src="//cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <style>
        body{
            margin:0;
            padding: 0;
        }
        #fileList>table>tbody>tr>.fileName span{
            opacity: 0;
            margin-left: 10px;
        }
        #fileList>table>tbody>tr>.fileName:hover span{
            opacity: 1;
        }
    </style>
</head>
<body>
    <section id="fileList">
        <table class="table table-striped">
            <thead></thead>
            <tbody>
                {foreach $fileList as $file}
                    {if in_array($file,array('.'))}{continue}{/if}
                    <tr>
                        <td>{$file}</td>
                        <td class="fileName">{$httpFileConfig[$file]}<span data-id="{$file}" class="btn btn-default">修改</span></td>
                        <td></td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
        <script>
            $('.fileName .btn').click(function(){
                var newName = prompt('请输入文件名');
                if(newName!==null){
                    $.post('httpAdminMetaAction.php',{
                        action:'rename',
                        name:$(this).data('id'),
                        title:newName
                    },function(data){
                        console.log(data);
                    });
                }
            });
        </script>
    </section>
</body>
</html>