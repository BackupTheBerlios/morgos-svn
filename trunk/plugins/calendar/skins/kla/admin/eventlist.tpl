<table>
	<tr>
		<th>Title</th>
		<th>Description</th>
		<th>Start</th>
		<th>End</th>
		<th>{t s="Edit"}</th>
	</tr>
	{foreach from=$events item='event'}
		<tr style="background-color: {$event.Group.Color};">
			<td>{$event.Title}</td>
			<td>{$event.Description}</td>
			<td>{$event.StartDate}</td>
			<td>{$event.EndDate}</td>
			<td><a href="{$event.EditLink|xhtml}"><img src="{$SkinPath}/images/icons/edit.png" alt="{t s="Edit"}" /></a></td>
		</tr>
	{/foreach}
</table>