<ol class="calendarLegend">
	<li class="weekend">{t s="Weekend"}</li>
	<li class="current">{t s="Today"}</li>
	<li class="othermonth">{t s="Other month"}</li>
	{foreach from=$groups item='group'}
		<li style="background-color: {$group.Color};">{$group.Name}</li>
	{/foreach}
</ol>