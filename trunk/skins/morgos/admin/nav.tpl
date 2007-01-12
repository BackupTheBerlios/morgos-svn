<ol class="menu" id="linktext">
	{foreach from=$MorgOS_Admin_RootMenu item='adminNav'}
	<li><hr /><a href="{$adminNav.Link|xhtml}">{$adminNav.Title}</a></li>		
	{/foreach}		
</ol>