{include file="header.tpl"}
<div class="content">
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
			<th>Maandag</th>
			<th>Dinsdag</th>
			<th>Woensdag</th>
			<th>Donderdag</th>
			<th>Vrijdag</th>
			<th>Zaterdag</th>
			<th>Zondag</th>
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
									<li style="background-color: {$event.Group.Color};">{$event.Title}</li>
							{/foreach}
						</ul>
					{/if}
				</td>
			{/foreach}
			</tr>
		{/foreach}
	</table>
</div>
{include file="footer.tpl"}