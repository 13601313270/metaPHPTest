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
    <ul id="myTab" class="nav nav-tabs">
        <li class="active"><a href="#home" data-toggle="tab">页面</a></li>
        <li><a href="#ios" data-toggle="tab">运行环境git状态</a></li>
        <li class="dropdown">
            <a href="#" id="myTabDrop1" class="dropdown-toggle"
               data-toggle="dropdown">Java
                <b class="caret"></b>
            </a>
            <ul class="dropdown-menu" role="menu" aria-labelledby="myTabDrop1">
                <li><a href="#jmeter" tabindex="-1" data-toggle="tab">jmeter</a></li>
                <li><a href="#ejb" tabindex="-1" data-toggle="tab">ejb</a></li>
            </ul>
        </li>
    </ul>
    <div id="myTabContent" class="tab-content">
        <div class="tab-pane fade in active" id="home">
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
            </section>
        </div>
        <div class="tab-pane fade" id="ios" style="padding-top: 10px;">
            <section id="github" class="container">
                <div class="panel panel-default">
                    <div class="panel-body">
                        当前正在<span id="githubState"></span>版本
                        <div class="btn-group">
                            <button id="githubStateChange" type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                切换到 <span class="caret"></span>
                            </button>
                            <ul id="floatDom" class="dropdown-menu"></ul>
                        </div>
                        <button type="button" class="btn btn-default">重置</button>
                    </div>
                </div>
                <script>
                    $('#githubStateChange').click(function(){
                        $.post('httpAdminMetaAction.php',{
                            action:'getBranch',
                        },function(data){
                            data = JSON.parse(data);
                            $('#floatDom').html('');
                            for(var i=0;i<data.length;i++){
                                var branchItem = data[i];
                                if(branchItem.substring(0,1)!=='*'){
                                    if(branchItem.substring(0,16)!=='  remotes/origin'){
                                        $('#floatDom').append('<li><a href="#" value="'+branchItem.substring(2)+'">本地:'+branchItem.substring(2)+'</a></li>');
                                    }
                                }
                            }
                            $('#floatDom').append('<li role="separator" class="divider"></li>');
                            for(var i=0;i<data.length;i++){
                                var branchItem = data[i];
                                if(branchItem.substring(0,1)!=='*'){
                                    if(branchItem.substring(0,16)=='  remotes/origin'){
                                        $('#floatDom').append('<li><a href="#" value="'+branchItem.substring(2)+'">公共:'+branchItem.substring(17)+'</a></li>');
                                    }
                                }
                            }
                            $('#floatDom').append('<li role="separator" class="divider"></li>');
                            $('#floatDom').append('<li><a href="#" active="pull">分支找不到?需要点击同步一下远程</a></li>');

//                            <li role="separator" class="divider"></li>
//                                    <li><a href="#">Separated link</a></li>
                        });
                    });
                    $('#floatDom').on('click','>li',function(){
                        var selectBranch = $(this).find('>a').attr('value');
                        if(selectBranch==undefined){
                            $.post('httpAdminMetaAction.php',{
                                action:'updateBranch',
                                sName:selectBranch
                            },function(data){
                                data = JSON.parse(data);
                                console.log(data);
                                initGitState();
                            });
                        }else{
                            $.post('httpAdminMetaAction.php',{
                                action:'checkout',
                                sName:selectBranch
                            },function(data){
                                data = JSON.parse(data);
                                console.log(data);
                                initGitState();
                            });
                        }
                        console.log(selectBranch);
                    });
                    function initGitState(){
                        $.post('httpAdminMetaAction.php',{
                            action:'getBranch',
                        },function(data){
                            data = JSON.parse(data);
                            for(var i=0;i<data.length;i++){
                                var branchItem = data[i];
                                if(branchItem.substring(0,1)=='*'){
                                    $('#githubState').html(branchItem.substring(2));
                                }
                            }
                        });
                    }
                    initGitState();
                </script>
            </section>
        </div>
        <div class="tab-pane fade" id="jmeter">
            <p>jMeter 是一款开源的测试软件。它是 100% 纯 Java 应用程序，用于负载和性能测试。</p>
        </div>
        <div class="tab-pane fade" id="ejb">
            <p>Enterprise Java Beans（EJB）是一个创建高度可扩展性和强大企业级应用程序的开发架构，部署在兼容应用程序服务器（比如 JBOSS、Web Logic 等）的 J2EE 上。
            </p>
        </div>
    </div>
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