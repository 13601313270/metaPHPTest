{extends file='template/main.layout.tpl'}
{block name="nav"}
    这个是导航
{/block}
{block name=body}
    <div class="row">
        <div class="col-xs-6">
            <div class="row">1</div>
            <div class="row">2</div>
        </div>
        <div class="col-xs-6">菜单</div>
    </div>
    <div class="row">
        我的HTML页面内容在这里{$article.title}
    </div>
{/block}