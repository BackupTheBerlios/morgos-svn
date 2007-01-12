<table>
	<tr>
		<th>{t s="Title"}</th>
		<th>{t s="Description"}</th>
		<th>{t s="Start"}</th>
		<th>{t s="End"}</th>
		<th>{t s="Edit"}</th>
		<th>{t s="Delete"}</th>
	</tr>
	{foreach from=$events item='event'}
		<tr style="background-color: {$event.Group.Color};">
			<td>{$event.Title}</td>
			<td>{$event.Description}</td>
			<td>{$event.StartDate}</td>
			<td>{$event.EndDate}</td>
			<td><a href="{$event.EditLink|xhtml}"><img src="{$SkinPath}/images/icons/edit.png" alt="{t s="Edit"}" /></a></td>
			<td><a href="{$event.DeleteLink|xhtml}" onclick="return confirm ('{t s="Are you sure you want to delete %1?" a=$event.Title}')"><img src="{$SkinPath}/images/icons/delete.png" alt="{t s="Delete"}" /></a></td>
		</tr>
	{/foreach}
</table>