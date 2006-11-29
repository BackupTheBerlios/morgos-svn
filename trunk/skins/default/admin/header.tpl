<html>
	<head>
		<link rel="stylesheet" type="text/css" 
			href="{$SkinPath}/styles/grayish/style.css" />	
		<link rel="stylesheet" type="text/css" 
			href="{$SkinPath}/styles/grayish/admin.css" />	
	
		<title>{$MorgOS_AdminPage_Title}</title>
		{$MorgOS_Admin_ExtraHead}
	</head>
	<body>
		<div id="header">
		<img class="logo" image="{$SkinPath}/images/logo.png" /><h1>{$MorgOS_AdminTitle}</h1>
		{include file="admin/navigation.tpl"}
		</div>
		<div id="main">
		{include file="admin/usermessages.tpl"}