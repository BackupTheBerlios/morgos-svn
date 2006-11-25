<html>
	<head>
		<link rel="stylesheet" type="text/css" 
			href="{$SkinPath}/styles/grayish/style.css" />	
	
		<title>{$MorgOS_CurrentPage_Title}</title>
		{$MorgOS_ExtraHead}
	</head>
	<body>
		<div id="header">
		<img class="logo" image="{$SkinPath}/images/logo.png" /><h1>{$MorgOS_SiteTitle}</h1>
		{include file="navigation.tpl"}
		</div>
		<div id="main">
		{include file="usermessages.tpl"}