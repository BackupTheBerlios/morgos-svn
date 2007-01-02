{foreach from=$MorgOS_Errors item='error'}
	{$error}
{/foreach}

{foreach from=$MorgOS_Warnings item='warning'}
	{$warning}
{/foreach}
	
{foreach from=$MorgOS_Notices item='notice'}
	{$notice}
{/foreach}