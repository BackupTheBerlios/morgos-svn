{include file="admin/header.tpl"}
	<h1>{$MorgOS_CurrentAdminPage.Title}</h1>

	<p>{$MorgOS_CurrentAdminPage.Content}</p>
	<table>
		<h2>{t s="Enabled plugins"}</h2>
		{include file="admin/pluginlist.tpl" Plugins=$MorgOS_EnabledPlugins}
		
		<h2>{t s="Disabled plugins"}</h2>
		{include file="admin/pluginlist.tpl" Plugins=$MorgOS_DisabledPlugins}
	</table>
{include file="admin/footer.tpl"}