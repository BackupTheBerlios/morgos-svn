{include file="admin/header.tpl"}
	<div id="main">
		<h1>{$MorgOS_CurrentAdminPage.Title}</h1>
		<p>{$MorgOS_CurrentAdminPage.Content}</p>
		
		<p>{foreach from=$MorgOS_PageLevel item='LevelName' name='level'}
			{$LevelName}
			{if ! $smarty.foreach.level.last}
				>>
			{/if}
		{/foreach}
		</p>
		
		<table>
			<tr>
				<th>{t s='Page title'}</th>
				<th>{t s='Place in menu'}</th>
				<th>{t s='View'}</th>
				<th>{t s='Delete'}</th>
				<th>{t s='Place as child'}</th>
			</tr>
		{foreach from=$MorgOS_PagesList item='childPage' name='pageslist'}
			<tr>
				<td><a href="index.php?action=adminPageManager&amp;pageID={$childPage.ID}">{$childPage.Title}</a></td>
				<td>
					{$childPage.PlaceInMenu}
					{if $smarty.foreach.pageslist.first and $smarty.foreach.pageslist.last}
						<!-- Cant move -->
					{elseif $smarty.foreach.pageslist.first}
						<a href="{"index.php?action=adminMovePageDown&pageID="|xhtml}{$childPage.ID|xhtml}">
							<img src="{$SkinPath}/images/icons/down.png" alt="{t s='Down'}"/>
						</a>
					{elseif $smarty.foreach.pageslist.last}
						<a href="{"index.php?action=adminMovePageUp&pageID="|xhtml}{$childPage.ID|xhtml}">
							<img src="{$SkinPath}/images/icons/up.png" alt="{t s='Up'}" style="margin-left: 20px;" />
						</a>
					{else}
						<a href="{"index.php?action=adminMovePageDown&pageID="|xhtml}{$childPage.ID|xhtml}">
							<img src="{$SkinPath}/images/icons/down.png" alt="{t s='Down'}" />
						</a>
						<a href="{"index.php?action=adminMovePageUp&pageID="|xhtml}{$childPage.ID|xhtml}">
							<img src="{$SkinPath}/images/icons/up.png" alt="{t s='Up'}"/>
						</a>
					{/if}
				</td>
				<td>
					<a href="{$childPage.Link|xhtml}">{t s="View"}</a>
				</td>
				<td>
					<a href="index.php?action=adminDeletePage&amp;pageID={$childPage.ID}" onclick="return confirm ('{t s="Are you sure you wan to delete %p" p=$childPage.Title}')">
						<img src="{$SkinPath}/images/icons/delete.png" alt="{t s='Delete'}"/>
					</a>
				</td>
				<td>
					<form action="index.php" method="get">
						{html_options name="newParentPageID" options=$childPage.PossibleNewParents}
						<input type="hidden" name="pageID" value="{$childPage.ID}" />
						<input type="hidden" name="action" value="adminMovePageLevelDown" />
						<input type="submit" value="{t s="Change parent"}" />
					</form>
				</td>
				<td>
					<a href="index.php?action=adminMovePageLevelUp&amp;pageID={$childPage.ID}"><img src="{$SkinPath}/images/icons/nivup.png" alt="{t s="Change up"}" /></a>
				</td>
			</tr>
		{/foreach}
		</table>

		<h3>{t s="Add a new page"}</h3>
		<form method="get" action="index.php">
			<p><label for="pageTitle">{t s="Pagetitle: "}</label><input type="text" name="pageTitle" id="pageTitle" /></p>
			<input type="hidden" name="parentPageID" value="{$MorgOS_ParentPage.ID}" />
			<input type="hidden" name="action" value="adminNewPage" />
			<input type="submit" value="{t s="Create a new page"}" />
		</form>		
		
		{if !$MorgOS_ParentPage.RootPage}
		<h3>{t s="Edit page details"}</h3>
		<form method="post" action="index.php">
			<p><label for="pageTitle">{t s="Pagetitle: "}</label><input type="text" name="pageTitle" id="pageTitle" value="{$MorgOS_ParentPage.Title}" /></p>
			<p><label for="pageNavTitle">{t s="Title for links: "}</label><input type="text" name="pageNavTitle" id="pageNavTitle" value="{$MorgOS_ParentPage.NavTitle}" /></p>
			<p><label for="pageContent">{t s="Pagecontent: "}</label>{include file="admin/pageeditor.tpl" name="pageContent" id="pageContent" curCont=$MorgOS_ParentPage.Content}</p>
			<input type="hidden" name="pageID" value="{$MorgOS_ParentPage.ID}" />
			<input type="hidden" name="action" value="adminSavePage" />
			<input type="submit" value="{t s="Save page"}" />
		</form>
		{/if}
	</div>
{include file="admin/footer.tpl"}