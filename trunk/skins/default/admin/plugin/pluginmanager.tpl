<table>
	<h3>{t s="Enabled plugins"}</h3>
	{include file="admin/plugin/pluginlist.tpl" Plugins=$MorgOS_EnabledPlugins}
		
	<h3>{t s="Disabled plugins"}</h3>
	{include file="admin/plugin/pluginlist.tpl" Plugins=$MorgOS_DisabledPlugins}

</table>