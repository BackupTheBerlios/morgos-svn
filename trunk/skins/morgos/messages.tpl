{foreach from=$MorgOS_Errors item='error'}
	<div class="error">
		<h3>{$error.Short}</h3>
		<p>{$error.Long}</p>
	</div>
{/foreach}

{foreach from=$MorgOS_Warnings item='warning'}
	<div class="warning">
		<h3>{$warning.Short}</h3>
		<p>{$warning.Long}</p>
	</div>
{/foreach}
	
{foreach from=$MorgOS_Notices item='notice'}
	<div class="notice">
		<h3>{$notice.Short}</h3>
		<p>{$notice.Long}</p>
	</div>
{/foreach}