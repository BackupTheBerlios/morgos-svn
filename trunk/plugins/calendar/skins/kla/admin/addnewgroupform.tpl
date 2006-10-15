<form action="index.php" method="get">
	<p><label for="groupName">{t s="Groupname: "}</label><input type="text" name="groupName" id="groupName" /></p>
	<p><label for="color">{t s="Colour: "}</label>
	<select name="groupColor" id="color">
		<option value="#ff6699">Roze</option>
		<option value="#FFFFCC">Lichtgeel</option>
		<option value="#00CCFF">Blauw</option>
		<option value="#ff6600">Rood</option>
		<option value="#00FF99">Appelblauwzeeegroen</option>
		<option value="#CCFF99">Groen</option>
		<option value="yellow">Geel</option>
	</select>
	</p>
	
	<input type="hidden" name="action" value="adminNewCalendarGroup" />
	<label>&nbsp;</label><input type="submit" value="{t s="New group"}" />
</form>