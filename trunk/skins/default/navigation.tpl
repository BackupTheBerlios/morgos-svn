<div id="nav">
	<ol>
		{foreach from=$MorgOS_RootMenu item='menuItem'}
			<li>
				<a href="{$menuItem.Link|xhtml}">{$menuItem.Title}</a>
				{if $menuItem.Childs}
					<ol>
						{foreach from=$menuItem.Childs item='subMenuItem'}
							<li>
								<a href="{$subMenuItem.Link|xhtml}">{$subMenuItem.Title}</a>
							</li>
						{/foreach}
					</ol>
				{/if}
			</li>
		{/foreach}
	</ol>
</div>