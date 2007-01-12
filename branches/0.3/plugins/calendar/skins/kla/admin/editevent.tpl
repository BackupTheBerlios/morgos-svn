{include file="admin/header.tpl"}
	<h1>{t s="Edit event"}</h1>
	
	<form action="index.php" method="get">
	<p>
		<label for="eventTitle">{t s="Title: "}</label>
		<input type="text" id="eventTitle" name="title" value="{$Calendar_Event.Title}" />
	</p>
	<p>
		<label for="startDate">{t s="Start of event: "}</label>
		{html_select_date prefix="Start_Date_" 
			field_order="DMY" month_extra="id=\"startDate\"" end_year='+2'
			time=$Calendar_Event.StartDate}
		{html_select_time prefix="Start_Time_" display_seconds=false minute_interval=5
			time=$Calendar_Event.StartDate}
	</p>
	<p>
		<label for="endDate">{t s="End of event: "}</label>
		{html_select_date prefix="End_Date_" field_order="DMY" month_extra="id=\"endDate\"" end_year='+2'
			time=$Calendar_Event.EndDate}
		{html_select_time prefix="End_Time_" display_seconds=false minute_interval=5
			time=$Calendar_Event.EndDate}
	</p>
	<p>
		<label for="eventDescription">{t s="Description: "}</label>
		<textarea id="eventDescription" name="description" rows="3" cols="30">{$Calendar_Event.Description}</textarea>
	</p>

	<p>
		<label for="eventGroup">{t s="Group: "}</label>
		<select name="groupID" id="eventGroup">{html_options options=$Calendar_AvGroups 
			selected=$Calendar_Event.group.ID}</select>
	</p>
	<input type="hidden" name="eventID" value="{$Calendar_Event.ID}" />	
	
	<input type="hidden" name="action" value="adminEditCalendarEvent" />
	<label>&nbsp;</label><input type="submit" value="{t s="Edit event"}" />
</form>
{include file="admin/footer.tpl"}
