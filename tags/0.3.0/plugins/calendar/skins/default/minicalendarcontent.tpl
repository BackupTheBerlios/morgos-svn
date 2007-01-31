<table class="minicalendar">
	<tr>
		<th colspan="4">&lt; {$Calendar_Month} &gt;</th>
		<th colspan="3">&lt; {$Calendar_Year} &gt;</th>
	</tr>
	<tr>
		<th>Ma</th>
		<th>Di</th>
		<th>Wo</th>
		<th>Do</th>
		<th>Vr</th>
		<th>Za</th>
		<th>Zo</th>
	</tr>
	{foreach from=$Calendar_Weeks item='week'}
		<tr>
		{foreach from=$week.Days item='day'}
			<td class="day">{$day.Nr}
				{if $day.Events}
					<ul class="events">
						{foreach from=$day.Events item='event'}
							<li>{$event.Title}: {$event.Description}</li>
						{/foreach}
					</ul>
				{/if}
			</td>
		{/foreach}
		</tr>
	{/foreach}
</table>