<html>
	<head>
		<title>{$MorgOS_CurrentPage_Title}</title>
		
		<!-- CSS FILES -->
		<link rel="stylesheet" type="text/css" href="{$SkinPath}/styles/default.css" />
		<!--[if IE]>
			<link rel="stylesheet" ref="{$SkinPath}/styles/default.hacks.IE.css" />
		<![endif]-->
	</head>
	<body>
		<div>
			<div id="header">
				<img src="{$MorgOS_Site_HeaderImage}" />
			</div>
			<div id="nav">
				<ol>
				{foreach from=$MorgOS_RootMenu item='menuItem'}
					<li><a href="{$menuItem.Link}">{$menuItem.Title}</a></li>
				{/foreach}
				</ol>
			</div>
			{include file="sidebar.tpl"}
	