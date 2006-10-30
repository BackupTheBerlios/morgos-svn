{include file="admin/header.tpl"}
	<h1>{$MorgOS_CurrentAdminPage.Title}</h1>
	<p>{$MorgOS_CurrentAdminPage.Content}</p>
	
	<p>{t s="Your configuration couldn't be saved. You could fix it by making config.php writable by PHP. For now save what follows in the file config.php"}</p>
	{t s="Start of config.php"}
	<pre>{$MorgOS_ConfigContent}</pre>
	{t s="End of config.php"}
	
	<p><a href="{$MorgOS_ConfigProceedLink}">{t s="If you save config.php click here to proceed."}</a></p>
{include file="admin/footer.tpl"}