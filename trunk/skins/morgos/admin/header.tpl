<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<title>{$MorgOS_AdminPage_Title}</title>
		
		<!-- CSS FILES -->
		<link rel="stylesheet" type="text/css" href="{$SkinPath}/styles/default.css" />
		<!--[IF IE lte 6]>
			<link rel="stylesheet" ref="{$SkinPath}/styles/default.hacks.IE6-55-5.css" />
		<![endif]-->
	</head>
	<body>
			<div id="header"><img src="{$SkinPath}/images/logo.png" width="80%" alt="logo" /></div>
		{include file="admin/nav.tpl"}
		{include file="admin/sidebar.tpl"}
		<div id="maintext">
		{include file="admin/messages.tpl"}