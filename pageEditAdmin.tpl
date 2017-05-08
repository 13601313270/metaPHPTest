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
    <ul id="myTab" class="nav nav-tabs">
        <li class="active"><a href="#home" data-toggle="tab">页面</a></li>
        <li><a href="#gitAdmin" data-toggle="tab"></a></li>
        <li><a href="#dataAdmin" id="dataAdminTab" data-toggle="tab"></a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane fade in active" id="home">
            <section id="fileList">

            </section>
        </div>
    </div>
    <section id="fileList">网页</section>
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
    <section style="width: 50%;height:400px;float: left;">
        <iframe id="tpl" src="http/{$file}" style="width: 100%;height:100%;border: solid 1px #b2b2b2;"></iframe>
    </section>
</body>
</html>