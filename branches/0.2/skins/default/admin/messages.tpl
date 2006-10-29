{foreach from=$MorgOS_Errors item='error'}
	<div class="error">{$error}</div>
{/foreach}

{foreach from=$MorgOS_Warnings item='warning'}
	<div class="warning">{$warning}</div>
{/foreach}
	
{foreach from=$MorgOS_Notices item='notice'}
	<div class="notice">{$notice}</div>
{/foreach}