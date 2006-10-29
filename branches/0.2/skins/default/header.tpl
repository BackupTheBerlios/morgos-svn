<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<title>{$MorgOS_CurrentPage_Title}</title>
		
		<!-- CSS FILES -->
		<link rel="stylesheet" type="text/css" href="{$SkinPath}/styles/default.css" />
		<!--[if IE]>
			<link rel="stylesheet" ref="{$SkinPath}/styles/default.hacks.IE.css" />
		<![endif]-->
		{$MorgOS_ExtraHead}
	</head>
	<body>
		<div>
			<div id="header">
				<img src="{$MorgOS_Site_HeaderImage}" alt="{t s="Logo"}"/>
			</div>
			<div id="nav">
				<ol>
				{foreach from=$MorgOS_RootMenu item='menuItem'}
					<li><a href="{$menuItem.Link|xhtml}">{$menuItem.Title}</a></li>
				{/foreach}
				</ol>
			</div>
			{include file="sidebar.tpl"}
	