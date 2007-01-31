<table>
	<tr>
		<th>Title</th>
		<th>Description</th>
		<th>Start</th>
		<th>End</th>
	</tr>
	{foreach from=$events item='event'}
		<tr style="background-color: {$event.Group.Color};">
			<td>{$event.Title}</td>
			<td>{$event.Description}</td>
			<td>{$event.Startdate}</td>
			<td>{$event.Enddate}</td>
		</tr>
	{/foreach}
</table>