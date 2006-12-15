<form action="index.php" method="post">
	<input type="hidden" name="action" value="adminChangeSiteSettings" />
	
	<p>
		<label for="siteTitle">{t s="Sitename: "}</label>
		<input type="text" name="siteTitle" id="siteTitle" value="{$MorgOS_SiteTitle}"/>
	</p>
	
	<p>
		<label for="enableUsers">{t s="Enable users: "}</label>
		<input type="checkbox" name="enableUsers" id="enableUsers"
			{if $MorgOS_AdminHome_EnableUsers}
			checked="checked"
			{/if}
			value="Y" />
	</p>
	
	<input type="submit" value="{t s="Save settings"}" />
</form>