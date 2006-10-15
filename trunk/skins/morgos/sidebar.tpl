<ol class="menu" id="linktext">
	{foreach from=$MorgOS_RootMenu item='menuItem' name='menu'}

	<li>
	{if !$smarty.foreach.menu.first}<hr />{/if}
	<a href="{$menuItem.Link|xhtml}">{$menuItem.Title}</a>
        {if $menuItem.Childs}
		<ol>
		
		    {foreach from=$menuItem.Childs item='childItem'}
			<li><a href="{$childItem.Link|xhtml}">{$childItem.Title}</a>
			{if $childItem.Childs}
			<ol>
			  {foreach from=$childItem.Childs item='subChildItem'}
			  <li><a href="{$subChildItem.Link|xhtml}">{$subChildItem.Title}</a></li>
			  {/foreach}
			</ol>
			{/if}
			</li>
			{/foreach}
		</ol>
		{/if}
     </li>		
	{/foreach}		
</ol>