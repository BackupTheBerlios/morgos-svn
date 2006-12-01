{foreach from=$MorgOS_Admin_Errors item='error'}
	{$error}
{/foreach}

{foreach from=$MorgOS_Admin_Warnings item='warning'}
	{$warning}
{/foreach}
	
{foreach from=$MorgOS_Admin_Notices item='notice'}
	{$notice}
{/foreach}