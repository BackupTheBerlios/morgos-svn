{include file="installer/header.tpl" step="3"}
<div>
	<form action="index.php" method="post">
		<fieldset>
			<legend>{t s="site information"}</legend>
			<p><label for="siteName">{t s="Sitename: "}</label>
				{morgos_input type="text" name="siteName" extra="id=\"siteName\" "}</p>
			<p><label for="siteDefaultLanguage">{t s="Default language: "}</label>
				{morgos_input type="text" name="siteDefaultLanguage" 
					extra="id=\"siteDefaultLanguage\" "}
			</p>
		</fieldset>	
		
		<fieldset>
			<legend>{t s="Admin information"}</legend>
			<p><label for="adminLogin">{t s="Admin login: "}</label>{morgos_input type="text" name="adminLogin" extra="id=\"adminLogin\" "}</p>
			<p><label for="adminPassword1">{t s="Admin password: "}</label>{morgos_input type="new_password" name="adminPassword1" extra="id=\"adminPassword1\" "}</p>
			<p><label for="adminPassword2">{t s="Admin password (repeat): "}</label>{morgos_input type="password" name="adminPassword2" extra="id=\"adminPassword2\" "}</p>
			<p><label for="adminMail">{t s="Admin mail: "}</label>{morgos_input type="text" name="adminMail" extra="id=\"adminMail\" "}</p>
		</fieldset>		
		
		<fieldset>
			<legend>{t s="Database information"}</legend>
			<p><label for="databaseModule">{t s="Database type: "}</label>
				<select id="databaseModule" name="databaseModule">
					{foreach from=$dbModules item='dbModule' key='dbName'}
						<option value="{$dbName}">{$dbName}</option>
					{/foreach}
				</select>
			</p>
			<p><label for="databaseHost">{t s="Database host: "}</label>{morgos_input type="text" name="databaseHost" extra="id=\"databaseHost\" "}</p>
			<p><label for="databaseUser">{t s="Database user: "}</label>{morgos_input type="text" name="databaseUser" extra="id=\"databaseUser\" "}</p>
			<p><label for="databasePassword">{t s="Database password: "}</label>{morgos_input type="password" name="databasePassword" extra="id=\"databasePassword\" "}</p>
			<p><label for="databaseName">{t s="Database name: "}</label>{morgos_input type="text" name="databaseName" extra="id=\"databaseName\" "}</p>
			<p><label for="databasePrefix">{t s="Database prefix: "}</label>{morgos_input type="text" name="databasePrefix" extra="id=\"databasePrefix\" "}</p>
		</fieldset>
	
	
		<input type="hidden" name="action" value="installerInstall" />
		<input type="submit" value="{t s="Install MorgOS >>"}" />
	</form>
</div>
{include file="installer/footer.tpl"}