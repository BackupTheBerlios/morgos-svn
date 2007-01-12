<table>	
	<tr>
		<th>{t s="Language"}</th>
		<th>{t s="Delete"}</th>
	</tr>
	{foreach from=$language item='language'}
	<tr>
		<td>{$language}</td>
		<td><a href="index.php?action=adminDeleteLanguage&amp;languageName={$language}">{t s="Delete"}</a></td>
	</tr>
	{/foreach}
</table>