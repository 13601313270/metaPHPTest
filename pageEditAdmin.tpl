<html>
<head>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no,minimal-ui">
    <link href="//cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <script src="//cdn.bootcss.com/jquery/3.2.1/jquery.js"></script>
    <script src="//cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="ace/src/ace.js" type="text/javascript" charset="utf-8"></script>
    <script src="ace/src/ext-language_tools.js"></script>
</head>
<body>
    <div id="actionProgress" class="progress" style="border-radius: 0;">
        <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
    </div>
    <div>
        <ul id="myTab" class="nav nav-tabs" style="padding: 0 5px;">
            <li class="active"><a href="#home" data-toggle="tab">页面</a></li>
            <li><a data-toggle="tab" href="#modAdmin">通用模块</a></li>
        </ul>
        <style>
            .tab-content{
                height:130px;
                border-bottom:1px solid #ddd;
                margin-bottom: 10px;
                overflow-x: scroll;
            }
            .tab-content>.tab-pane>.panel{
                float: left;
                margin: 3px;
            }
            .tab-content>.tab-pane>.panel>.panel-heading{
                padding: 3px 3px 3px 10px;
            }
            .tab-content>.tab-pane>.panel>.panel-body{
                padding: 3px;
            }
            .tab-content>.tab-pane .input-group{
                margin: 1px;
            }
        </style>
        <div class="tab-content">
            <div class="tab-pane fade in active" id="home" style="width: 908px;">
                <div class="panel panel-default" style="width:170px;">
                    <div class="panel-heading">操作</div>
                    <div class="panel-body">
                        <button class="btn btn-default" onclick="save()">
                            <span class="glyphicon glyphicon-floppy-saved" aria-hidden="true"></span>保存
                        </button>
                        <script>
                            function save(){
                                $.post('',{
                                    action:'save',
                                    content:editor.getValue(),
                                    file:'{$file}',
                                },function(data){
                                    console.log(data);
                                });
                            }
                        </script>
                    </div>
                </div>
                <div class="panel panel-default" style="width:360px;">
                    <div class="panel-heading">网址</div>
                    <div class="panel-body">
                        <div class="input-group">
                            <div class="input-group-addon">网址</div>
                            <input class="form-control" placeholder="网址">
                        </div>
                        <div class="input-group">
                            <div class="input-group-addon">php</div>
                            <input class="form-control" placeholder="php">
                        </div>
                    </div>
                </div>
                <div id="mastGet" class="panel panel-default" style="width:360px;">
                    <div class="panel-heading">必填参数</div>
                    <div class="panel-body">
                        {foreach $allGet as $column}
                            <div class="input-group">
                                <div class="input-group-addon">{$column}</div>
                                <input class="form-control" data-id="{$column}" placeholder="{$column}">
                            </div>
                        {/foreach}
                    </div>
                </div>
            </div>
            <style>
                #modAdmin{
                    padding-left: 5px;
                }
                #modAdmin>div{
                    width:180px;height:120px;float:left;
                }
                #modAdmin>div>.panel-body{
                    padding-left: 10px;
                }
            </style>
            <div class="tab-pane fade" id="modAdmin">
                {foreach $allModule as $mod}
                    <div class="panel panel-default" data-name="{$mod.name}">
                        <div class="panel-heading">{$mod.name}</div>
                        <div class="panel-body">
                            {foreach $mod.callArgs as $args}
                                <div data-name="{$args.name}" data-default="{$args.default}">{$args.name}:
                                    {if isset($args.default)}
                                        默认值{$args.default}
                                    {else}
                                        必填
                                    {/if}
                                </div>
                            {/foreach}
                        </div>
                    </div>
                {/foreach}
            </div>
            <script>
                {literal}
                $('#modAdmin>.panel').click(function(){
                    var returnStr = '';
                    //计算缩进位置
                    var selectRange = editor.getSelectionRange();
                    selectRange.start.row-=2;
                    var preText = editor.session.getTextRange(selectRange);
                    preText = preText.match(/(\n)(\s*).*\n(\s*)$/);
                    if(preText!==null){
                        var preLineTextTab = preText[2];
                        var selectRange = editor.getSelectionRange();
                        selectRange.start.column=0;
                        if( editor.session.getTextRange(selectRange).match(/^\s*$/) ){
                            editor.session.remove(selectRange);
                            returnStr += preLineTextTab;
                        }
                    }
                    //添加内容
                    returnStr += '{include file="'+$(this).data('name')+'.mod.tpl"';
                    $(this).find('>.panel-body>div').each(function(){
                        if($(this).data('default')){
                            returnStr += ' '+$(this).data('name')+"='"+$(this).data('default')+"'";
                        }else{
                            returnStr += ' '+$(this).data('name')+"='*'";
                        }
                    });
                    returnStr+='}';
                    editor.insert(returnStr);
                });
                {/literal}
            </script>
        </div>
    </div>
    <div style="position:fixed;bottom:0;top:215px;left:0;right:0;border-bottom: solid 1px #5d5d5d;">
        <section id="editor" style="width: 50%;height:100%;float: left;">{htmlspecialchars($tplFileContent)}</section>
        <script>
            //更新控制器推送数据和,生成的html
            function reloadDataAndLastHtml(){
                var allGet = {
                };
                $('#mastGet .panel-body input').each(function(){
                    allGet[$(this).data('id')] = $(this).val();
                });
                $.post('',{
                    action:'runData',
                    content:editor.getValue(),
                    line:editor.selection.getRange().start,
                    file:'{$file}',
                    simulate:allGet
                },function(data){
                    data = JSON.parse(data);
                    allComplate = data.pushResult;
                    var htmlList = data.html.match(/<html>\s*<head>([\S|\s]+)<\/head>\s*<body(\s[^>]*)?>([\S|\s]+)<\/body>\s*<\/html>/);
                    if(htmlList[2]!=undefined){
                        var bodyAttr = htmlList[2].split(' ');
                        for(var i=0;i<bodyAttr.length;i++){
                            var key = bodyAttr[i].match(/(\S+)=['|"]([\S|\s]+)['|"]$/);
                            if(key){
                                $($('#tpl')[0].contentDocument).find('body').attr(key[1],key[2]);
                            }
                        }
                    }
                    $($('#tpl')[0].contentDocument).find('head').html(htmlList[1]);
                    $($('#tpl')[0].contentDocument).find('body').html(htmlList[3]);
                });
            }
            $('#mastGet .panel-body input').on('change',function(){
                reloadDataAndLastHtml();
                var allParams = [];
                $('#mastGet .panel-body input').each(function(){
                    allParams.push($(this).data('id')+'='+$(this).val());
                });
                $('#tpl').attr('src','http/{$file}?'+allParams.join('&'));
            });
            var languageTools = ace.require("ace/ext/language_tools");
            var editor = ace.edit("editor");
            editor.$blockScrolling = Infinity;
            editor.setFontSize(16);
//            editor.getSession().setMode("ace/mode/html");
            editor.getSession().setMode("ace/mode/smarty");
            editor.setTheme("ace/theme/twilight");

            editor.setOptions({
                enableBasicAutocompletion: true,
                enableSnippets: true,
                enableLiveAutocompletion: true
            });

            editor.getSession().on('change', function(e) {
                if(e.action=='insert'){
//                    console.log(e.lines);
                }else{

                }
                reloadDataAndLastHtml();
            });
            var allComplate = [];
            languageTools.addCompleter({
                getCompletions: function (editor, session, pos, prefix, callback) {
                    var result = [];//搜索结果
                    for(var i in allComplate){
                        result.push({
                            name: i,
                            value: ('$'+i),//实际输出
                            caption: '页面数据:'+i,//搜索浮层展示
//                            meta: 'function',
                            type: "local",
                            score: 1000 // 让test排在最上面
                        });
                        for(var j in allComplate[i]){
                            result.push({
                                name: i,
                                value: ('$'+i),//实际输出
                                caption: '页面数据:'+i+'.'+j,//搜索浮层展示
//                            meta: 'function',
                                type: "local",
                                score: 999 // 让test排在最上面
                            });
                        }
                    }
                    callback(null,result);
                }
            });
        </script>
        <section id="pageShow" style="width: 50%;height:100%;float: left;position: relative;">
            <div id="tplSize" class="panel panel-default" style="margin:10px 10px 0;">
                <div class="panel-body" style="padding:5px;">
                    <div class="input-group" style="width:200px;float:left;margin: 2px;">
                        <div class="input-group-addon">宽度</div>
                        <input class="form-control" data-id="id" placeholder="模拟宽度" value="1080">
                    </div>
                    <div class="input-group" style="width:200px;float:left;margin: 2px;">
                        <div class="input-group-addon">高度</div>
                        <input class="form-control" data-id="chid" placeholder="模拟高度" value="720">
                    </div>
                </div>
            </div>
            <style>
                #split{
                    width: 10px;left:-5px;height:100%;position:absolute;background-color: black;opacity: 0;cursor: ew-resize;
                }
            </style>
            <div id="split"></div>
            <script>
                $('#split').mousedown(function() {
                    var float = $('<div style="position: fixed;width: 100%;height:100%;top:0;left:0;z-index: 9999"></div>');

                    function mouseMove(event) {
                        $('body').append(float);
                        var leftShowSplit = (event.pageX / document.documentElement.clientWidth * 100).toFixed(2);
                        $('#editor').width(leftShowSplit + '%');
                        $('#pageShow').width((100 - leftShowSplit) + '%');
                        initTplScroll();
                    }
                    $('body').mousemove(mouseMove);
                    $('body').mouseup(function () {
                        float.remove();
                        $("body").unbind("mousemove", mouseMove);
                    });
                });
                window.onbeforeunload=function(event){
                    return '正在编辑状态';
                }
            </script>
            <iframe id="tpl" src="http/{$file}" style="border: solid 1px #b2b2b2;"></iframe>
            <script>
                function initTplScroll(){
                    var webWidth = $('#tplSize input:eq(0)').val();
                    var webHeight = $('#tplSize input:eq(1)').val();
                    $('#tpl').css('width',webWidth);
                    $('#tpl').css('height',webHeight);
                    var rightwidth = $('#pageShow').width();
                    var padding=10;
                    var scale = (rightwidth-padding*2)/webWidth;
                    $('#tpl').css('transform','scale('+scale+')');
                    $('#tpl').css('marginLeft',(webWidth-rightwidth)/-2);
                    $('#tpl').css('marginTop',(1-scale)/-2*webHeight+10);
                    editor.resize();
                }
                initTplScroll();
                $(window).resize(function() {
                    initTplScroll();
                });
            </script>
        </section>
    </div>
</body>
</html>