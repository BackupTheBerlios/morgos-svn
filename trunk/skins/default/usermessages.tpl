{foreach from=$MorgOS_Errors item='error'}
	{$error.Long}
{/foreach}

{foreach from=$MorgOS_Warnings item='warning'}
	{$warning.Long}
{/foreach}
	
{foreach from=$MorgOS_Notices item='notice'}
	{$notice.Long}
{/foreach}