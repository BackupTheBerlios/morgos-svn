<table>
	<tr>
		<th>{t s="Name"}</th>
		<th>{t s="Edit"}</th>
		<th>{t s="Delete"}</th>
	</tr>
	{foreach from=$groups item='group'}
		<tr style="background-color: {$group.Color};">
			<td>{$group.Name}</td>
			<td>
				<form action="index.php" method="post">
				<input type="text" name="groupName" value="{$group.Name}" size="8"/>
				<select name="groupColor">
				{include file="admin/colorlist.tpl" assigned=$group.Color}
				</select>
				
				<input type="hidden" name="action" value="adminEditCalendarGroup" />
				<input type="hidden" name="groupID" value="{$group.ID}" />
				<input type="submit" value="{t s="Save"}" />
				</form>
			</td>
			<td><a href="{$group.DeleteLink|xhtml}" onclick="return confirm ('{t s="Are you sure you want to delete %1?" a=$group.Name}')"><img src="{$SkinPath}/images/icons/delete.png" alt="{t s="Delete"}" /></a></td>
		</tr>	
	{/foreach}
</table>