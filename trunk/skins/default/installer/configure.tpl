{include file="installer/header.tpl" step="3/5"}
<div>
	<form action="index.php" method="post">
		<fieldset>
			<legend>{t s="site information"}</legend>
			<p><label for="siteName">{t s="Sitename: "}</label><input type="text" id="siteName" name="siteName" /></p>
		</fieldset>	
		
		<fieldset>
			<legend>{t s="Admin information"}</legend>
			<p><label for="adminLogin">{t s="Admin login: "}</label><input type="text" id="adminLogin" name="adminLogin" /></p>
			<p><label for="adminPassword1">{t s="Admin password: "}</label><input type="password" id="adminPassword1" name="adminPassword1" /></p>
			<p><label for="adminPassword2">{t s="Admin password (repeat): "}</label><input type="password" id="adminPassword2" name="adminPassword2" /></p>
			<p><label for="adminMail">{t s="Admin mail: "}</label><input type="text" id="adminMail" name="adminMail" /></p>
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
			<p><label for="databaseHost">{t s="Database host: "}</label><input type="text" id="databaseHost" name="databaseHost" /></p>
			<p><label for="databaseUser">{t s="Database user: "}</label><input type="text" id="databaseUser" name="databaseUser" /></p>
			<p><label for="databasePassword">{t s="Database password: "}</label><input type="password" id="databasePassword" name="databasePassword" /></p>
			<p><label for="databaseName">{t s="Database name: "}</label><input type="text" id="databaseName" name="databaseName" /></p>
			<p><label for="databasePrefix">{t s="Database prefix: "}</label><input type="text" id="databasePrefix" name="databasePrefix" /></p>
		</fieldset>
	
	
		<input type="hidden" name="action" value="installerInstall" />
		<input type="submit" value="{t s="Install MorgOS >>"}" />
	</form>
</div>
{include file="installer/footer.tpl"}