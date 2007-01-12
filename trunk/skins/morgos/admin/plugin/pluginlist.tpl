<table>
	<tr>
		<th>{t s="Plugin name"}</th>
		<th>{t s="Version"}</th>
		<th>{t s="Load"}</th>
		<th>{t s="(Un)Install"}</th>
	</tr>
	{foreach from=$plugins item="plugin"}
		<tr>
			<td>{$plugin.Name}</td>
			<td>{$plugin.Version}</td>
			<td>
				{if $plugin.Enabled}
					<a href="{$plugin.DisableLink|xhtml}">{t s="Disable"}</a>						
				{elseif $plugin.Compatible}
					<a href="{$plugin.EnableLink|xhtml}">{t s="Enable"}</a>
				{else}
					<span class="error">{$plugin.CompatibleMessage}</span>
				{/if}
			</td>
			<td>
				{if $plugin.Installable}
				{if $plugin.Installed}
					<a href="{$plugin.UnInstallLink|xhtml}">{t s="Uninstall plugin"}</a>
				{else}
					<a href="{$plugin.InstallLink|xhtml}">{t s="Install plugin"}</a>
				{/if}
				{/if}
			</td>
		</tr>			
	{/foreach}
</table>