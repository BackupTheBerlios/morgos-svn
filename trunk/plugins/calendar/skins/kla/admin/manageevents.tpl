{include file="admin/header.tpl"}
	<div class="content">
		<h1>{$MorgOS_CurrentAdminPage.Title}</h1>
		
		{include file="admin/messages.tpl"}
		<p>{$MorgOS_CurrentAdminPage.Content}</p>
		
		<h2>{t s="Current events"}</h2>
		{include file="admin/eventlist.tpl" events=$Calendar_CurrentEvents}		
		
		<h2>{t s="Upcoming events"}</h2>
		{include file="admin/eventlist.tpl" events=$Calendar_UpcomingEvents}
		
		<h2>{t s="Create a new event"}</h2>
		{include file="admin/addneweventform.tpl"}
	</div>
{include file="admin/footer.tpl"}
