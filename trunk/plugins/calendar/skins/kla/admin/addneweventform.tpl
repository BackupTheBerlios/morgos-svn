<form action="index.php" method="get">
	<p>
		<label for="eventTitle">{t s="Title: "}</label>
		<input type="text" id="eventTitle" name="title" />
	</p>
	<p>
		<label for="startDate">{t s="Start of event: "}</label>
		{html_select_date prefix="Start_Date_" field_order="DMY" month_extra="id=\"startDate\"" end_year='+2'}
		{html_select_time prefix="Start_Time_" display_seconds=false minute_interval=5}
	</p>
	<p>
		<label for="endDate">{t s="End of event: "}</label>
		{html_select_date prefix="End_Date_" field_order="DMY" month_extra="id=\"endDate\"" end_year='+2'}
		{html_select_time prefix="End_Time_" display_seconds=false minute_interval=5}
	</p>
	<p>
		<label for="eventDescription">{t s="Description: "}</label>
		<textarea id="eventDescription" name="description" rows="3" cols="30"></textarea>
	</p>

	<p>
		<label for="eventGroup">{t s="Group: "}</label>
		<select name="groupID" id="eventGroup">{html_options options=$Calendar_AvGroups}</select>
	</p>
	
	<input type="hidden" name="action" value="adminNewCalendarEvent" />
	<label>&nbsp;</label><input type="submit" value="{t s="Add event"}" />
</form>