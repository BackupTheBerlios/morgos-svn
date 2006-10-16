{include file="admin/header.tpl"}
	<h1>{$MorgOS_CurrentAdminPage.Title}</h1>
	<p>{$MorgOS_CurrentAdminPage.Content}</p>
	
	<h2>{t s="Legend"}</h2>	
	{include file="legend.tpl" groups=$Calendar_Groups}
	
	<h2>{t s="Current events"}</h2>
	{include file="admin/eventlist.tpl" events=$Calendar_CurrentEvents}		
		
	<h2>{t s="Upcoming events"}</h2>
	{include file="admin/eventlist.tpl" events=$Calendar_UpcomingEvents}
	
	<h2>{t s="Create a new event"}</h2>
	{include file="admin/addneweventform.tpl"}
	
	<h2>{t s="Create a new group"}</h2>
	{include file="admin/addnewgroupform.tpl"}
{include file="admin/footer.tpl"}
