<style>
    #bottom{
        width:100%; height:100px; background-color: grey;padding: 20px;
    }
    #bottom>div{
        float: left;height: 30px;margin-right: 10px;
    }
    #bottom>div>a{
        color:black;
    }
</style>
<div id="bottom">
    <div>友情链接:</div>
    {foreach $fiendLinks as $link}
        <div><a target="_blank" href="{$link[1]}">{$link[0]}</a></div>
    {/foreach}
</div>