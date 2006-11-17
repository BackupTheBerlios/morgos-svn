<html>
	<head>
		<title>{t s="Fatal Error"}</title>
	</head>
	<body>
		<h1>{t s="Fatal Error"}</h1>
		<div class="errror">
			<p>{$MorgOS_Error}</p>
			{if $MorgOS_PreviousLink} 
				<a href="{$MorgOS_PreviousLink}">Go back</a>
			{/if}
		</div>
	</body>
</html>