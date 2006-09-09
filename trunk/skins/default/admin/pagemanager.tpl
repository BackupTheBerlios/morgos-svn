{include file="admin/header.tpl"}
	<div id="main">
		<h1>{$MorgOS_CurrentAdminPage->getGenericName()}</h1>
		<p>{$MorgOS_CurrentAdminPage->getGenericContent()}</p>
		
		<table>
			<tr>
				<th>Naam</th>
				<th>Plaats in menu</th>
			</tr>
		{foreach from=$MorgOS_PagesList item='childPage'}
			<tr>
				<td>{$childPage->getGenericName()}</td>
				<td>{$childPage->getPlaceInMenu()}</td>
			</tr>
		{/foreach}
		</table>
	</div>
{include file="admin/footer.tpl"}