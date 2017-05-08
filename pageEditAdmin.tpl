<html>
<head>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no,minimal-ui">
    <link href="//cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <script src="//cdn.bootcss.com/jquery/3.2.1/jquery.js"></script>
    <script src="//cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

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
</body>
</html>