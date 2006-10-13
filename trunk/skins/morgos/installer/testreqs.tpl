{include file="installer/header.tpl" step="2/5"}
<div>
	<p>{t s="Checking your PHP version..."}
	{if !$phpError}
		<span class="ok">{$phpMessage}</span>
	{else}
		<span class="not_ok">{$phpMessage}</span>
	{/if}</p>
	
	<p>{t s="Checking for database modules..."}
	{if !$dbMError}
		<span class="ok">{$dbMMessage}</span>
	{else}
		<span class="not_ok">{$dbMMessage}</span>
	{/if}</p>
	
	<p>{t s="Checking files and directories..."}
	{if !$dirsError}
		<span class="ok">{$dirsMessage}</span>
	{else}
		<span class="not_ok">{$dirsMessage}</span>
	{/if}</p>


	<form action="index.php" method="get">
		<input type="hidden" name="action" value="askConfig" />
		{if $canRun}
			<input type="hidden" name="canRun" value="Y" />
			<input type="submit" value="{t s="Next >>"}" id="submitAgree" />
		{else}
			<input type="submit" value="{t s="Next >>"}" id="submitAgree" disabled="disabled" />
		{/if}
	</form>
	
	<form action="index.php" method="get">
		<input type="hidden" name="action" value="installerShowRequirements" />
		<input type="hidden" name="agreed" value="Y" />
		<input type="submit" value="{t s="Recheck"}" />
	</form>
</div>
{include file="installer/footer.tpl"}