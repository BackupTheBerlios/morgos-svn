{include file="admin/header.tpl"}
	<div id="main">
		<h1>{$MorgOS_CurrentAdminPage.Title}</h1>
		
		{include file="admin/messages.tpl"}

		<p>{$MorgOS_CurrentAdminPage.Content}</p>
		<table>
			<tr>
				<th>{t s="Plugin name"}</th>
				<th>{t s="Version"}</th>
				<th>{t s="Load"}</th>
				<th>{t s="(Un)Install"}</th>
			</tr>
			{foreach from=$MorgOS_AvailablePlugins item="plugin"}
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
						{if $plugin.Installed}
							<a href="{$plugin.UnInstallLink|xhtml}">{t s="Uninstall plugin"}</a>
						{else}
							<a href="{$plugin.InstallLink|xhtml}">{t s="Install plugin"}</a>
						{/if}
					</td>
				</tr>			
			{/foreach}
		</table>
	</div>
{include file="admin/footer.tpl"}