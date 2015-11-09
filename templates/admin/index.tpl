{include file="box-header.tpl" title=$lang.rate_titles}
<form action="{$smarty.const.IA_SELF}" method="post">
    {preventCsrf}
    <table border="0" cellspacing="0" cellpadding="0" class="striped">
        {section name=star loop=$core.config.ratings_max_star}
        {assign var="index" value=$smarty.section.star.index+1}
        <tr>
            <td>{lang key='rate_this'} {$index}</td>
            <td><input type="text" class="common" name="rate[{$index}]" value="{if isset($ratings_texts.$index)}{$ratings_texts.$index}{/if}"></td>
        </tr>
        {/section}
        <tr><td colspan="2"><input type="submit" class="common" value="{lang key='save_changes'}"></td></tr>
    </table>
    <input type="hidden" value="save_texts" name="action">
</form>
{include file="box-footer.tpl" class="box"}