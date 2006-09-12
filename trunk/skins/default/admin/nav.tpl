<div id="nav">
	<ol>
	{foreach from=$MorgOS_AdminNav item='adminNav'}
		<li>
			<a href="{$adminNav->getLink()}">{$adminNav->getName()}</a>
		</li>
	{/foreach}
	</ol>
</div>