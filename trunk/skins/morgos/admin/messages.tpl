<div id="mess">
{foreach from=$MorgOS_Errors item='error'}
	<div class="error"><img src="{$SkinPath}/images/icons/!.gif" alt="{t s="Error"}" />{$error}</div>
{/foreach}

{foreach from=$MorgOS_Warnings item='warning'}
	<div class="warning"><img src="{$SkinPath}/images/icons/q.gif" alt="{t s="Warning"}"/>{$warning}</div>
{/foreach}
	
{foreach from=$MorgOS_Notices item='notice'}
	<div class="notice"><img src="{$SkinPath}/images/icons/3..gif" alt="{t s="Notice"}"/>{$notice}</div>
{/foreach}
</div>