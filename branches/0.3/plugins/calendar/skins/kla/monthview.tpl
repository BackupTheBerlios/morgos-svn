{include file="header.tpl"}
	<h1>{$MorgOS_CurrentPage_Title}</h1>
	{$MorgOS_CurrentPage_Content}
	
	{include file="legend.tpl" groups="$Calendar_Groups"}
	<table class="monthcalendar">
		<tr>
			<th colspan="5">
				<a href="{$Calendar_Month.PreviousLink|xhtml}">&lt;</a>
				{$Calendar_Month.Text}
				<a href="{$Calendar_Month.NextLink|xhtml}">&gt;</a></th>
			<th colspan="3">
				<a href="{$Calendar_Year.PreviousLink|xhtml}">&lt;</a>
				{$Calendar_Year.Text}
				<a href="{$Calendar_Year.NextLink|xhtml}">&gt;</a>
			</th>
		</tr>
		<tr>
			<th>&nbsp;</th>
			{foreach from=$Calendar_WeekDays item='day'}
				<th>{$day}</th>
			{/foreach}
		</tr>
		{foreach from=$Calendar_Weeks item='week'}
			<tr>
				<th>{$week.Nr}</th>
			{foreach from=$week.Days item='day'}
				{if $day.current}
					{assign var='class' value='current'}
				{elseif $day.othermonth and $day.weekend}
					{assign var='class' value='othermonth_weekend'}
				{elseif $day.othermonth}
					{assign var='class' value='othermonth'}
				{elseif $day.weekend}
					{assign var='class' value='weekend'}
				{else}
					{assign var='class' value='day'}
				{/if}
				<td class="{$class}"><p>{$day.Nr}</p>
					{if $day.Events}
						<ul class="events">
							{foreach from=$day.Events item='event'}
									<li style="background-color: {$event.Group.Color};">
										<a href="{$event.MonthMoreInfoLink|xhtml}" onClick="showEvent ({$event.ID}); return false;">{$event.Title}</a>
									</li>
							{/foreach}
						</ul>
					{/if}
				</td>
			{/foreach}
			</tr>
		{/foreach}
	</table>
	<div id="eventBox">
		{if $Calendar_CurrentEvent}
			<span>{$Calendar_CurrentEvent.StartDate|date_format:"%d/%m %H:%m"} -- {$Calendar_CurrentEvent.EndDate|date_format:"%d/%m %H:%m"}</span>
			<span>{$Calendar_CurrentEvent.Name}</span>
			<p>
			{$Calendar_CurrentEvent.Description}
			</p>
		{else}
		{t s="Click on an event to show more information about it."}
		{/if}
	</div>
{include file="footer.tpl"}