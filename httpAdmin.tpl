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
        #console{
            position:fixed;
            width: 100%;
            bottom:0px;
            margin-bottom: 1px;
        }
        #console .panel-body{
            padding: 0 15px;
        }
        #console .panel-body .accordion-inner{
            max-height: 300px;
            overflow-y: scroll;
        }
    </style>
</head>
<body>
    <div id="actionProgress" class="progress" style="height: 10px;border-radius: 0;">
        <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
    </div>
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
            {literal}
            $('.fileName .btn').click(function(){
                var newName = prompt('请输入文件名');
                if(newName!==null){
                    $('#actionProgress>div').css('width','0%');
                    var interval = setInterval(function(){
                        var nowPosition = parseInt($('#actionProgress>div').attr('aria-valuenow'));
                        nowPosition++;
                        if(nowPosition<90){
                            $('#actionProgress>div').attr('aria-valuenow',nowPosition);
                            $('#actionProgress>div').css('width',(nowPosition+1)+'%');
                        }else{
                            clearInterval(interval);
                        }
                    },100);
                    $.post('httpAdminMetaAction.php',{
                        action:'rename',
                        name:$(this).data('id'),
                        title:newName
                    },function(data){
                        data = data.replace(/\n/g,'<br/>');
                        data = data.replace(/\s/g,'&nbsp;');
                        $('#console .panel-body .accordion-inner').append($('<div>'+data+'</div>'));
                        clearInterval(interval);
                        $('#actionProgress>div').css('width','100%');
                        location.href = location.href;
                    });
                }
            });
            {/literal}
        </script>
    </section>
    <section id="console"  class="panel panel-default">
        <div class="panel-heading" data-toggle="collapse" href="#collapseOne">操作日志</div>
        <div class="panel-body">
            <div id="collapseOne" class="accordion-body collapse">
                <div class="accordion-inner">
                </div>
            </div>
            {*<div>*}
                {*<input type="email" class="form-control" id="exampleInputEmail1" placeholder="Email">*}
            {*</div>*}
        </div>
    </section>
</body>
</html>