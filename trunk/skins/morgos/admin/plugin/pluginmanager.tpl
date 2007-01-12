<h2>{t s="Enabled plugins"}</h2>
	{include file="admin/plugin/pluginlist.tpl" plugins=$MorgOS_EnabledPlugins}
<h2>{t s="Disabled plugins"}</h2>
	{include file="admin/plugin/pluginlist.tpl" plugins=$MorgOS_DisabledPlugins}