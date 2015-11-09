{if $ratings_allow}
	<div style="padding: 10px; border:1px solid black; margin: 10px 0;">
	<form action="{$smarty.const.IA_URL}ratings.json" method="get" id="rate_item">
		<div style="float:left">
			<b>{lang key="ratings"}: (<span id="rate_num">{$rate.rate}</span>)</b>
		</div>
		{section name=star loop=$core.config.ratings_max_star}
			{assign var="index" value=$smarty.section.star.index+1}
			<input name="rate_item" type="radio" class="star" value="{$index}" {if $index == $rate.cur_rate}checked="checked"{/if} title="{if isset($ratings_texts.$index)}{$ratings_texts.$index}{else}{lang key='rate_this'} {$index}{/if}">
		{/section}
		<div style="float: left; margin: 0 10px;" id="hover-text">&nbsp;</div>
		<div style="clear:both;line-height:1px;overflow:hidden;height:1px;">&nbsp;</div>
	</form>
	</div>
	
	{ia_add_js}
	{literal}
	$(function(){
		var tip = $('#hover-text');
		$('#rate_item .star').rating({
			callback: function(value, link){
				$.getJSON(intelli.config.ia_url+'ratings.json?{/literal}item={$ratings_item}&id={$ratings_listing}&title={$ratings_title}&url={$ratings_url}{literal}&star='+value, function(json){
					intelli.notifBox({
						id: 'notification',
						type: json.error?'error':'success',
						msg: [json.msg]
					});
					if(json.rate)
					{
						$('#rate_num').html(json.rate);
					}
				});
			},
			focus: function(value, link){
				tip[0].data = tip[0].data || tip.html();
				tip.html(link.title || 'value: '+value);
			},
			blur: function(value, link){
				tip.html(tip[0].data || '');
			}
		});
	});
	{/literal}
	{/ia_add_js}
	{ia_print_js files="_IA_URL_plugins/ratings/js/frontend/jquery.MetaData,_IA_URL_plugins/ratings/js/frontend/jquery.rating"}
	{ia_print_css files="_IA_URL_plugins/ratings/templates/front/css/jquery.rating"}
{/if}