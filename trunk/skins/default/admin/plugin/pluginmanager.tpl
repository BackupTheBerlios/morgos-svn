<h1>{$MorgOS_CurrentAdminPage.Title}</h1>

		<p>{$MorgOS_CurrentAdminPage.Content}</p>
		
		<h2>{t s="Enabled plugins"}</h2>
			{include file="admin/plugin/pluginlist.tpl" plugins=$MorgOS_EnabledPlugins}
		<h2>{t s="Disabled plugins"}</h2>
			{include file="admin/plugin/pluginlist.tpl" plugins=$MorgOS_DisabledPlugins}