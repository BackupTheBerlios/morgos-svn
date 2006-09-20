<div id="sidebar">
	<div class="box">
		<h3>{t s='Menu'}</h3>
	{foreach from=$MorgOS_Menu item='menuItem'}
		<div class="menu">
			<h4><a href="{$menuItem.Link}">{$menuItem.Title}</a></h4>
			{if $menuItem.Childs}
				<ul>
					{foreach from=$menuItem.Childs item='childItem'}
						<li><a href="{$childItem.Link}">{$childItem.Title}</a></li>
					{/foreach}
				</ul>
			{/if}
		</div>
	{/foreach}
	</div>
</div>