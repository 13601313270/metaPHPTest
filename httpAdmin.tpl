<html>
<head>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no,minimal-ui">
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
        .dropdown-menu>ul {
            padding: 0;
        }
        .dropdown-menu>ul>li{
            list-style: none;
        }
        .dropdown-menu>ul>li>a {
            display: block;
            padding: 3px 20px;
            clear: both;
            font-weight: 400;
            line-height: 1.42857143;
            color: #333;
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
            cursor: pointer;
        }
        .dropdown-menu>ul>li>a:hover {
            color: #262626;
            text-decoration: none;
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
    <div id="actionProgress" class="progress" style="height: 10px;border-radius: 0;">
        <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
    </div>
    <ul id="myTab" class="nav nav-tabs">
        <li class="active"><a href="#home" data-toggle="tab">页面</a></li>
        <li><a href="#gitAdmin" data-toggle="tab">运行环境git状态</a></li>
        <li><a id="dataAdminTab" href="#dataAdmin" data-toggle="tab">数据</a></li>
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
        <div class="tab-pane fade" id="gitAdmin" style="padding-top: 10px;">
            <section id="github" class="container">
                <div class="panel panel-default">
                    <div class="panel-body">
                        当前正在<span id="githubState"></span>版本
                        <div class="btn-group">
                            <button id="githubStateChange" type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                切换到分支<span class="caret"></span>
                            </button>
                            <ul id="floatDom" class="dropdown-menu"></ul>
                        </div>
                        <div class="btn-group">
                            <button id="commitlog" type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                重置到节点<span class="caret"></span>
                            </button>
                            <div class="dropdown-menu" style="height:400px;overflow-y: scroll;width: 400px;">
                                <canvas style="position:absolute;left:0;"></canvas>
                                <ul id="checkoutCommit"></ul>
                            </div>
                        </div>
                        <button id="githubClean" type="button" class="btn btn-default">重置</button>
                        <button id="githubPull" type="button" class="btn btn-default">拉取</button>
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
                        });
                    });
                    function checkout(){
                        var selectBranch = $(this).find('>a').attr('value');
                        beginProgress(2);
                        if(selectBranch==undefined){
                            $.post('httpAdminMetaAction.php',{
                                action:'updateBranch',
                                sName:selectBranch
                            },function(data){
                                data = JSON.parse(data);
                                initGitState();
                                stopProgress();
                            });
                        }else{
                            $.post('httpAdminMetaAction.php',{
                                action:'checkout',
                                sName:selectBranch
                            },function(data){
                                if(data!=''){
                                    data = JSON.parse(data);
                                }
                                initGitState();
                                stopProgress();
                            });
                        }
                    }
                    $('#floatDom').on('click','>li',checkout);
                    $('#checkoutCommit').on('click','>li',checkout);
                    $('#githubPull').click(function(){
                        beginProgress(4);
                        $.post('httpAdminMetaAction.php',{
                            action:'pull'
                        },function(data){
                            data = JSON.parse(data);
                            initGitState();
                            stopProgress();
                        });
                    });
                    {literal}
                    $('#commitlog').click(function(){
                        $('#commitlog').parent().find('ul').html('');
                        beginProgress(2);
                        $.post('httpAdminMetaAction.php',{
                            action:'commitlog'
                        },function(data){
                            data = JSON.parse(data);
                            var commithashList = {};
                            for(var i=0;i<data.length;i++){
                                data[i] = data[i].match(/^([\*|\\|\||\/|\s]+)((\S{7}) \(([^\)]*)\) \(([^\)]+)\) (\S+) (\S+ \S+))?/).slice(3);
                                if(data[i][0]!=undefined){
                                    var parent = data[i][1].split(' ');
                                    commithashList[data[i][0]] = {
                                        parent:parent,
                                        title:data[i][2],
                                        author:data[i][3],
                                        time:data[i][4],
                                        child:[],
                                    }
                                }
                            }
                            for(var i in commithashList){
                                for(var j=0;j<commithashList[i].parent.length;j++){
                                    if(commithashList[i].parent[j]!==''){
                                        commithashList[commithashList[i].parent[j]].child.push(i);
                                    }
                                }
                            }
                            var isTabUse = [];//是否占着tab位
                            var isTabUseColor = [];
                            var isTabMaxUse = 0;
                            $('#commitlog').parent().find('.dropdown-menu>canvas').attr('height', data.length*26  );
                            $('#commitlog').parent().find('.dropdown-menu>canvas').attr('width',30);
                            var cxt=$('#commitlog').parent().find('.dropdown-menu>canvas')[0].getContext("2d");
                            cxt.clearRect(0,0,cxt.canvas.width,cxt.canvas.height);
                            function getRandomColor(){
                                function getPer(){
                                    return '0123456789abcdef'[Math.floor(Math.random()*16)];
                                }
                                var colorBase = ['ff','97',getPer()+getPer()];
                                colorBase = colorBase.sort(function(){ return 0.5 - Math.random() })
                                return '#'+colorBase.join('');
                            }
                            for(var i=0;i<data.length-1;i++){
                                var commitHash = data[i][0];
                                if(commitHash!=undefined){
                                    var childList = commithashList[commitHash].child;
                                    if(childList && childList.length>0){
                                        if(childList.length>1){//从上到下合并
                                            var childPos = isTabUse.indexOf(childList[0]);
                                            for(var j=1;j<childList.length;j++){
                                                childPos = Math.min(childPos,isTabUse.indexOf(childList[j]));
                                            }
                                            isTabUse[childPos] = commitHash;
                                            for(var j=0;j<childList.length;j++){
                                                var temp = isTabUse.indexOf(childList[j]);
                                                if(temp!=-1 && temp!=childPos){
                                                    isTabUse[temp] = null;
                                                }
                                            }
                                        }
                                        else{
                                            if(commithashList[childList[0]].parent.length>1){//从上到下分叉
                                                if(commithashList[childList[0]].parent.indexOf(commitHash)==0){
                                                    var childPos = isTabUse.indexOf(childList[0]);
                                                }else{
                                                    for(var j=0;j<=isTabUse.length;j++){
                                                        if(isTabUse[j]==undefined || isTabUse[j]==null){
                                                            var childPos = j;break;
                                                        }
                                                    }
                                                    isTabUseColor[childPos] = getRandomColor();
                                                }
                                            }else{//从上到下直线
                                                var childPos = isTabUse.indexOf(childList[0]);
                                            }
                                            isTabUse[childPos] = commitHash;
                                        }
                                    }else{
                                        isTabUse[0] = commitHash;
                                        isTabUseColor[0] = getRandomColor();
                                    }
                                    if(isTabUse.length>isTabMaxUse){
                                        isTabMaxUse = isTabUse.length;
                                        (function(){
                                            var tepData = cxt.getImageData(0,0,cxt.canvas.width,cxt.canvas.height);
                                            $('#commitlog').parent().find('.dropdown-menu>canvas').attr('width',isTabMaxUse*10+4);
                                            cxt.putImageData(tepData,0,0);
                                        })();
                                    }
                                    //展示这一行
                                    var show = 0;
                                    for(var j=0;j<isTabUse.indexOf(commitHash);j++){
                                        show ++;
                                    }
                                    commithashList[commitHash].tab = show;
                                    commithashList[commitHash].line = $('#commitlog').parent().find('ul li').length;
                                    (function(){
                                        var centerX = show*10+8;
                                        var centerY = $('#commitlog').parent().find('ul li').length*26+13;
                                        cxt.lineWidth=2;
                                        cxt.strokeStyle="#000000";
                                        cxt.fillStyle=isTabUseColor[show];//"#42768b";
                                        for(var j=0;j<childList.length;j++){
                                            var centerX2 = commithashList[childList[j]].tab*10+8;
                                            var centerY2 = commithashList[childList[j]].line*26+13;

                                            if(commithashList[childList[j]].tab==show){
                                                cxt.moveTo(centerX,centerY-4);
                                                cxt.lineTo(centerX,centerY2+13);
                                                cxt.lineTo(centerX2,centerY2+4);
                                            } else if(centerX2>centerX){
                                                cxt.moveTo(centerX+3,centerY);
                                                cxt.lineTo(centerX2,centerY-13);
                                                cxt.lineTo(centerX2,centerY2+4);
                                            }else{
                                                cxt.moveTo(centerX,centerY);
                                                cxt.lineTo(centerX,centerY2+13);
                                                cxt.lineTo(centerX2+3,centerY2);
                                            }
                                            cxt.stroke();
                                        }
                                        cxt.beginPath();
                                        cxt.arc(centerX,centerY,4,0,Math.PI*2,true);
                                        cxt.closePath();
                                        cxt.stroke();
                                        cxt.fill();
                                    })();
                                    $('#commitlog').parent().find('ul').append('<li><a value="'+commitHash+'">'+commitHash+' '+commithashList[commitHash].title+'</a></li>');
                                }
                            }
                            $('#commitlog').parent().find('ul').css('paddingLeft',isTabMaxUse*10);
                            var tepData = cxt.getImageData(0,0,cxt.canvas.width,cxt.canvas.height);
                            $('#commitlog').parent().find('.dropdown-menu>canvas').attr('height', $('#commitlog').parent().find('ul li').length*26  );
                            $('#commitlog').parent().find('.dropdown-menu>canvas').attr('width',isTabMaxUse*10+4);
                            cxt.putImageData(tepData,0,0);
                            stopProgress();
                        });
                    });
                    {/literal}
                    $('#githubClean').click(function(){
                        beginProgress(2);
                        $.post('httpAdminMetaAction.php',{
                            action:'githubClean'
                        },function(data){
                            data = JSON.parse(data);
                            initGitState();
                            stopProgress();
                        });
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
        <style>
            {literal}
            #dataAdmin{margin: 10px auto;}
            #dataAdmin>.panel{float: left;margin: 0 5px 20px 5px;height: 200px;}
            @media screen and (min-width: 1200px) {
                #dataAdmin{width:1040px;}
                #dataAdmin>.panel{width: 250px;}
            }
            @media screen and (min-width: 900px) and (max-width: 1199px){
                #dataAdmin{width:840px;}
                #dataAdmin>.panel{width: 200px;}
            }
            @media screen and (min-width: 700px) and (max-width: 899px){
                #dataAdmin{width:640px;}
                #dataAdmin>.panel{width: 200px;}
            }
            @media screen and (max-width: 699px){
                #dataAdmin{width:90%;}
                #dataAdmin>.panel{width: 100%;margin: 0 0 20px;}
            }
            #showTableColumn table [data-id=maxLength] input{
                max-width: 60px;min-width:37px;padding:6px;text-align: center;
            }
            #showTableColumn table tbody tr td:first-child label{
                line-height: 34px;
            }
            #showTableColumn table tbody tr td:first-child span{
                border:solid 1px #a96449;
                border-radius: 4px;
                color: #a96449;
                cursor: pointer;
                display: none;
            }
            #showTableColumn table tbody tr:hover td:first-child span{
                display:inline-block;
            }
            {/literal}
        </style>
        <div class="tab-pane fade" id="dataAdmin">
            <script>
                var allDataType = null;
                function getDataTypes(nowType,allType){
                    if(allType!==undefined){
                        allDataType = allType;
                    }
                    var html = '<select class="form-control">';
                    for(var i=0;i<allDataType.length;i++){
                        if(allDataType[i].type==nowType){
                            html+= '<option selected value="'+allDataType[i].type+'">'+allDataType[i].name+'</option>';
                        }else{
                            html+= '<option value="'+allDataType[i].type+'">'+allDataType[i].name+'</option>';
                        }
                    }
                    html+= '</select>';
                    return html;
                }
                {*var allTableApiClass = {json_encode($tableApiClass)};*}
                $('#dataAdminTab').click(function(){
                    $.post('mysqlAction.php',{
                        action:'tables',
                        databases:'{$useDataBases[0]}'
                    },function(data){
                        $('#dataAdmin').html('');
                        data = JSON.parse(data);
                        for(var i=0;i<data.length;i++){
                            $('#dataAdmin').append('<div class="panel panel-default" data-database="'+data[i].database+'" data-name="'+data[i].Name+'">'+
                                '<div class="panel-heading">'+data[i].Name+'</div>'+
                                '<div class="panel-body">'+
                                    '<p>创建于'+data[i].Create_time+'</p>'+
                                    '<p>使用引擎'+data[i].Engine+'</p>'+
                                    '<p>数据量'+data[i].Rows+'</p>'+
                                    '<p>'+data[i].Comment+'</p>'+
                                '</div>'+
//                                '<div class="panel-footer">Panel footer</div>'+
                            '</div>');
                        }
                    });
                });
                //初始化表后台信息
                function initTableInfo(database,tableName){
                    $.post('mysqlAction.php',{
                        action:'getDataApi',
                        database:database,
                        name:tableName,
//                        tableApi:allTableApiClass
                    },function(data){
                        data = JSON.parse(data);
                        var className = data.className;
                        $.post('mysqlAction.php',{
                            action:'showTableAdmin',
                            class:className
                        },function(data){
                            data = JSON.parse(data);
                            $('#showTableColumn').show();
                            $('#showTableColumn').attr('data-id',className);
                            $('#showTableColumn>.panel>.panel-body').html('');
                            var table = $('<table class="table"><thead><tr>' +
                                    '<th style="min-width: 100px">字段</th>' +
                                    '<th style="min-width: 100px">名称</th>' +
                                    '<th style="min-width: 100px">类型</th>' +
                                    '<th style="min-width: 100px">最大长度</th>' +
                                    '<th style="min-width: 100px">是否必填</th>' +
                                    '<th style="min-width: 100px">默认值</th>' +
                                    '</tr></thead><tbody></tbody></table>');
                            for(var i in data.option){
                                table.append($('<tr data-id="'+i+'">' +
                                        '<td><label>'+i+'<span class="glyphicon glyphicon-remove" aria-hidden="true"></span></label></td>' +
                                        '<td data-id="title"><input class="form-control" value="'+data.option[i].title+'"/></td>' +
                                        '<td data-id="dataType">'+getDataTypes(data.option[i].dataType,data.allMysqlColType)+'</td>' +
                                        '<td data-id="maxLength" data-value="'+data.option[i].maxLength+'">'+
                                        (['varchar','char'].indexOf(data.option[i].dataType)>-1?(
                                                '<input type="number" class="form-control" value="'+data.option[i].maxLength+'">'
                                        ):'')+'</td>' +
                                        '<td data-id="notNull"><input class="form-control" type="checkbox" '+(data.option[i].notNull?'checked':'')+'>'+'</td>' +
                                        '<td data-id="default"><input class="form-control" value="'+(data.option[i].default!==undefined?data.option[i].default:'')+'">'+'</td>' +
                                        '</tr>'));
                            }
                            $('#showTableColumn>.panel>.panel-body').append(table);
                            $('[data-id=adminFileName]').attr('href',data.adminFileName);
                        });

                    });
                }
                $('#dataAdmin').on('click','>.panel',function(){
                    var database = $(this).data('database');
                    var tableName = $(this).data('name');
                    initTableInfo(database,tableName);
                });
            </script>
        </div>
        <div class="tab-pane fade" id="ejb">
            <p>Enterprise Java Beans（EJB）是一个创建高度可扩展性和强大企业级应用程序的开发架构，部署在兼容应用程序服务器（比如 JBOSS、Web Logic 等）的 J2EE 上。</p>
        </div>
    </div>
    <script>
        {literal}
        function beginProgress(time){
            $('#actionProgress>div').css('width','0%');
            window.interval = setInterval(function(){
                var nowPosition = parseFloat($('#actionProgress>div').attr('aria-valuenow'));
                nowPosition +=(1/time);
                if(nowPosition<90){
                    $('#actionProgress>div').attr('aria-valuenow',nowPosition);
                    $('#actionProgress>div').css('width',parseInt(nowPosition)+'%');
                }else{
                    clearInterval(interval);
                }
            },10);
        }
        function stopProgress(){
            clearInterval(interval);
            $('#actionProgress>div').css('width','100%');
            setTimeout(function(){
                $('#actionProgress>div').css('width','0%');
            },1000);
        }
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
    <style>
        {literal}
        #showTableColumn{
            position: fixed;background-color: rgba(141, 141, 141, 0.48);left:0;top:0;width:100%;height:100%;
        }
        #showTableColumn>.panel{  position:absolute;  }
        @media screen and (min-width: 700px){
            #showTableColumn>.panel{ left:calc(50% - 336px);width:672px;}
        }
        @media screen and (max-width: 699px){
            #showTableColumn>.panel{ left: 2%;width:96%;}
        }
        @media screen and (min-height: 700px){
            #showTableColumn>.panel{ top: calc(50% - 330px);height:616px;}
        }
        @media screen and (max-height: 699px){
            #showTableColumn>.panel{ top: 2%;height:88%;}
        }
        {/literal}
    </style>
    <section id="showTableColumn" style="display: none;">
        <div class="panel panel-default">
            <div class="panel-heading">
                表后台
                <div class="btn-group btn-group-xs" role="group" aria-label="..." style="float: right;">
                    <button type="button" class="btn btn-default" onclick="addColumn()">插入新字段</button>
                    <a class="btn btn-default" data-id="adminFileName" target="_blank">后台</a>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            放到tab
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="#">移除</a></li>
                            <li><a href="#">Dropdown link</a></li>
                            <li><a href="#">最后</a></li>
                        </ul>
                    </div>
                </div>
                <script>
                    function addColumn(){
                        $('#showTableColumn .panel-body>table>tbody').append($('<tr>' +
                            '<td data-id="name"><input class="form-control"/></td>' +
                            '<td data-id="title"><input class="form-control" value=""/></td>' +
                            '<td data-id="dataType">'+getDataTypes('int')+'</td>' +
                            '<td data-id="maxLength"></td>' +
                            '<td data-id="notNull"><input class="form-control" type="checkbox">'+'</td>' +
                            '<td data-id="default"><input class="form-control"></td>' +
                        '</tr>'));
                    }
                </script>
            </div>
            <div class="panel-body" style="position: absolute;top:41px;bottom: 56px;width: 100%;overflow-y: scroll"></div>
            <div class="panel-footer" style="position: absolute;bottom: 0;width: 100%;">
                <button id="saveColumn" type="button" class="btn btn-default">保存</button>
                <button type="button" onclick="$('#showTableColumn').hide()" class="btn btn-default">取消</button>
                <script>
                    $('#saveColumn').click(function(){
                        var allColumnTr = $(this).parents('.panel').find('>.panel-body>table>tbody>tr');
                        var nowSate = {
                        };
                        allColumnTr.each(function(){
                            var columnName = $(this).data('id');
                            if(columnName==undefined){
                                columnName = $(this).find('>[data-id=name] input').val();
                            }
                            nowSate[columnName] = {
                            };
                            $(this).find('>[data-id]').each(function(){
                                var shuxingName = $(this).data('id');
                                if($(this).find('>.form-control').attr('type') == 'checkbox'){
                                    var shuxingValue = $(this).find('>.form-control').is(':checked');
                                }else if($(this).find('>.form-control').attr('type') == 'number'){
                                    var shuxingValue = parseInt($(this).find('>.form-control').val());
                                }else{
                                    var shuxingValue = $(this).find('>.form-control').val();
                                }
                                nowSate[columnName][shuxingName] = shuxingValue;
                            });
                        });
                        $.post('mysqlAction.php',{
                            action:'updateTableAdmin',
                            databases:'{$useDataBases[0]}',
                            table:$('#showTableColumn').data('id'),
                            option:nowSate,
                        },function(data){
                            console.log(data);
                        });
                    });
                    $('#showTableColumn').on('click','table tbody tr td .glyphicon',function(){
                        $(this).parents('tr').remove();
                    });
                </script>
            </div>
        </div>
    </section>
    <script>
        //切换分类
        $('#showTableColumn').on('change','table [data-id=dataType]',function(){
            var selectType = $(this).find('select').val();
            var maxLengthTd = $(this).parents('tr').find('[data-id=maxLength]');
            console.log(   $(this).parents('tr').find('[data-id=maxLength]')   );
            if(['varchar','char'].indexOf(selectType)>-1){
                maxLengthTd.html('<input type="number" class="form-control" value="'+maxLengthTd.data('value')+'">');
            }else{
                maxLengthTd.html('');
            }
            console.log(selectType);
        });
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