{include file="admin/header.tpl"}
	<div id="main">
		<h1>{$MorgOS_CurrentAdminPage->getName()}</h1>
		<p>{$MorgOS_CurrentAdminPage->getContent()}</p>
		
		<table>
			<tr>
				<th>{t s='Page title'}</th>
				<th>{t s='Place in menu'}</th>
				<th>{t s='View'}</th>
			</tr>
		{foreach from=$MorgOS_PagesList item='childPage' name='pageslist'}
			<tr>
				<td><a href="index.php?action=adminPageManager&amp;pageID={$childPage->getID()}">{$childPage->getName()}</a></td>
				<td>
					{$childPage->getPlaceInMenu()}
					{if $smarty.foreach.pageslist.first and $smarty.foreach.pageslist.last}
						<!-- Cant move -->
					{elseif $smarty.foreach.pageslist.first}
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
				<td>
					<a href="{$childPage->getLink()|xhtml}">View</a>
				</td>
			</tr>
		{/foreach}
		</table>
		
		<h3>{t s="Add a new page"}</h3>
		<form method="get" action="index.php">
			<p><label for="pageTitle">{t s="Pagetitle: "}</label><input type="text" name="pageTitle" id="pageTitle" /></p>
			<input type="hidden" name="parentPageID" value="{$MorgOS_ParentPage->getID()}" />
			<input type="hidden" name="action" value="adminNewPage" />
			<input type="submit" value="{t s="Create a new page"}" />
		</form>		
		
		{if !$MorgOS_ParentPage->isRootPage()}
		<h3>{t s="Edit page details"}</h3>
		<form method="post" action="index.php">
			<p><label for="pageTitle">{t s="Pagetitle: "}</label><input type="text" name="pageTitle" id="pageTitle" value="{$MorgOS_ParentPage->getName()}" /></p>
			<p><label for="pageContent">{t s="Pagecontent: "}</label>{include file="admin/pageeditor.tpl" name="pageContent" id="pageContent" curCont=$MorgOS_ParentPage->getContent()}</p>
			<input type="hidden" name="pageID" value="{$MorgOS_ParentPage->getID()}" />
			<input type="hidden" name="action" value="adminSavePage" />
			<input type="submit" value="{t s="Save page"}" />
		</form>
		{/if}
	</div>
{include file="admin/footer.tpl"}