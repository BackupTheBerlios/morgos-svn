{include file="installer/header.tpl" step="3"}
<h2>{t s="Installation was succesfull"}</h2>
<p>{t s="We couldn't write to config.php so your website will not run. Please save the following text in config.php"}<p>
<pre>{$CONFIG_CONTENT}</pre>
<a href="index.php">{t s="Now you can proceed"}</a>
{include file="installer/footer.tpl"}