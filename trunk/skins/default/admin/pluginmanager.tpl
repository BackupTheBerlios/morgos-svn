{include file="admin/header.tpl"}
	<div id="main">
		<h1>{$MorgOS_CurrentAdminPage.Title}</h1>
		
		{include file="admin/messages.tpl"}

		<p>{$MorgOS_CurrentAdminPage.Content}</p>
		<table>
			<tr>
				<th>{t s="Plugin name"}</th>
				<th>{t s="Load"}</th>
				<!--<th>{t s="Uninstall"}</th>-->
			</tr>
			{foreach from=$MorgOS_AvailablePlugins item="plugin"}
				<tr>
					<td>{$plugin.Name}</td>
					<td>
						{if $plugin.Enabled}
							<a href="{$plugin.DisableLink|xhtml}">{t s="Disable"}</a>						
						{elseif $plugin.Compatible}
							<a href="{$plugin.EnableLink|xhtml}">{t s="Enable"}</a>
						{else}
							<span class="error">{$plugin.CompatibleMessage}</span>
						{/if}
					</td>
					<!--<td>
					
						{$plugin.UninstallLink}</td>-->
				</tr>			
			{/foreach}
		</table>
	</div>
{include file="admin/footer.tpl"}