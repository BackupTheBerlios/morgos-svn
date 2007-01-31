<div id="nav">
	<ol>
	{foreach from=$MorgOS_AdminNav item='adminNav'}
		<li>
			<a href="{$adminNav.Link|xhtml}">{$adminNav.Title}</a>
		</li>
	{/foreach}
	</ol>
</div>