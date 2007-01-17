{table name="testTable" headers="ID;Name;Comment" data=$UserData}
	{table_custom_header header='Comment'}{if false}{else}Comment translated{/if}{/table_custom_header}
	{table_custom_header header='ID'}Could be translated{/table_custom_header}
{/table}