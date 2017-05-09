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
            <li><a data-toggle="tab" href="#gitAdmin">第二个</a></li>
            <li><a data-toggle="tab" href="#dataAdmin">第三个</a></li>
        </ul>
        <style>
            .tab-content{
                height:130px;
                border-bottom:1px solid #ddd;
                margin-bottom: 10px;
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
            <div class="tab-pane fade in active" id="home">
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
                <script>
                    $('#mastGet .panel-body input').on('change',function(){
                        var allParams = [];
                        $('#mastGet .panel-body input').each(function(){
                            allParams.push($(this).data('id')+'='+$(this).val());
                        });
                        $('#tpl').attr('src','http/{$file}?'+allParams.join('&'));
                    })
                </script>
            </div>
            <div class="tab-pane fade" id="gitAdmin">
                <section>2</section>
            </div>
            <div class="tab-pane fade" id="dataAdmin">
                <section>3</section>
            </div>
        </div>
    </div>

    <section id="editor" style="width: 50%;height:400px;float: left;">{htmlspecialchars($tplFileContent)}</section>
    <script>
        var languageTools = ace.require("ace/ext/language_tools");
        var editor = ace.edit("editor");
        editor.$blockScrolling = Infinity;
        editor.setFontSize(16);
        editor.getSession().setMode("ace/mode/html");
        editor.setTheme("ace/theme/twilight");

        editor.setOptions({
            enableBasicAutocompletion: true,
            enableSnippets: true,
            enableLiveAutocompletion: true
        });
        languageTools.addCompleter({
            getCompletions: function (editor, session, pos, prefix, callback) {
                console.log(prefix);
                callback(null, [
                    {
                        name: "test",
                        value: "test(sadfadsf)",
                        caption: "testcap",
                        meta: 'function',
                        type: "local",
                        score: 1000 // 让test排在最上面
                    }
                ]);
            }
        });
    </script>
    <section id="pageShow" style="width: 50%;height:400px;float: left;position: relative;">
        <style>
            #split{
                width: 10px;left:-5px;height:100%;position:absolute;background-color: black;opacity: 0;cursor: ew-resize;
            }
            #split:hover{
                opacity: 1;
            }
        </style>
        <div id="split"></div>
        <script>
            $('#split').mousedown(function(){
                var float = $('<div style="position: fixed;width: 100%;height:100%;top:0;left:0;z-index: 9999"></div>');
                function mouseMove(event){
                    $('body').append(float);
                    var leftShowSplit = (event.pageX/document.documentElement.clientWidth*100).toFixed(2);
                    $('#editor').width(leftShowSplit+'%');
                    $('#pageShow').width((100-leftShowSplit)+'%');
//                    pageShow
                }
                $('body').mousemove(mouseMove);
                $('body').mouseup(function(){
                    float.remove();
                    $("body").unbind("mousemove",mouseMove);
                });
            });
        </script>
        <iframe id="tpl" src="http/{$file}" style="width: 100%;height:100%;border: solid 1px #b2b2b2;"></iframe>
    </section>
</body>
</html>