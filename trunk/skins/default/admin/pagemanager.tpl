{include file="admin/header.tpl"}
	<div id="main">
		<h1>{$MorgOS_CurrentAdminPage->getName()}</h1>
		<p>{$MorgOS_CurrentAdminPage->getContent()}</p>
		
		<table>
			<tr>
				<th>{t s='Page title'}</th>
				<th>{t s='Place in menu'}</th>
			</tr>
		{foreach from=$MorgOS_PagesList item='childPage' name='pageslist'}
			<tr>
				<td>{$childPage->getName()}</td>
				<td>
					{$childPage->getPlaceInMenu()}
					{if $smarty.foreach.pageslist.first}
						<a href="{"index.php?action=adminMovePageDown&pageID="|xhtml}{$childPage->getID()|xhtml}">
							<img src="{$SkinPath}/images/icons/down.png" alt="{t s='Down'}"/>
						</a>
					{elseif $smarty.foreach.pageslist.last}
						<a href="{"index.php?action=adminMovePageUp&pageID="|xhtml}{$childPage->getID()|xhtml}">
							<img src="{$SkinPath}/images/icons/up.png" alt="{t s='Up'}" style="margin-left: 20px;" />
						</a>
					{else}
						<a href="{"index.php?action=adminMovePageDown&pageID="|xhtml}{$childPage->getID()|xhtml}">
							<img src="{$SkinPath}/images/icons/down.png" alt="{t s='Down'}" />
						</a>
						<a href="{"index.php?action=adminMovePageUp&pageID="|xhtml}{$childPage->getID()|xhtml}">
							<img src="{$SkinPath}/images/icons/up.png" alt="{t s='Up'}"/>
						</a>
					{/if}
				</td>
			</tr>
		{/foreach}
		</table>
	</div>
{include file="admin/footer.tpl"}