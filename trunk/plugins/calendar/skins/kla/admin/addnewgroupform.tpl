<form action="index.php" method="get">
	<p><label for="groupName">{t s="Groupname: "}</label><input type="text" name="groupName" id="groupName" /></p>
	<p><label for="color">{t s="Colour: "}</label>
	<select name="groupColor" id="color">
		{include file="admin/colorlist.tpl"}
	</select>
	</p>
	
	<input type="hidden" name="action" value="adminNewCalendarGroup" />
	<label>&nbsp;</label><input type="submit" value="{t s="New group"}" />
</form>