<h3>{t s="General configuration"}</h3>
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

<h3>{t s="Language configuration"}</h3>
{include file="admin/languagelist.tpl" language=$MorgOS_AvailableContentLanguages}
<h4>{t s="New language"}</h4>
<form action="index.php" method="post">
	<p>
		<label for="languageName">{t s="Language: "}</label>
		<input type="text" name="languageName" id="languageName" value=""/>
	</p>

	<input type="hidden" name="action" value="adminInstallLanguage" />
	<input type="submit" value="{t s="Add new language"}" />
</form>