{extends file='template/main.layout.tpl'}
{block name=body}
    我的HTML页面内容在这里{$article.title}
    {include file="commonModule/temp.mod.tpl" iId=1}
{/block}