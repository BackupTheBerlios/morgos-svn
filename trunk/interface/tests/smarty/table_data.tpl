{table name="testTable" headers="ID;Name;LastMessage" data=$UserData}

	{table_data_element header=LastMessage item='user'}<a href="?id=$user.LastMessageID}">$user.LastMessageText}</a>{/table_data_element}

{/table}