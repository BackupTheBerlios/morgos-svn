<ol class="menu" id="linktext">
	{foreach from=$MorgOS_AdminNav item='adminNav'}
	<li><hr /><a href="{$adminNav.Link|xhtml}">{$adminNav.Title}</a></li>		
	{/foreach}		
</ol>