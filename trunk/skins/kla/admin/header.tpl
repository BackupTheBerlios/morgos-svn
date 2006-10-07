<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<title>{$MorgOS_CurrentAdminPage.Title}</title>
		
		<!-- CSS FILES -->
		<link rel="stylesheet" type="text/css" href="{$SkinPath}/styles/standaard/basis.css" />
		<link rel="stylesheet" type="text/css" href="{$SkinPath}/styles/standaard/menu.css" />
		<link rel="stylesheet" type="text/css" href="{$SkinPath}/styles/standaard/admin.css" />
		<!--[IF IE lte 6]>
			<link rel="stylesheet" ref="{$SkinPath}/styles/default.hacks.IE6-55-5.css" />
		<![endif]-->
	</head>
	<body>
		<div class="header">
			<h1>MorgOS Admin</h1>
			{include file="admin/nav.tpl"}
		</div>
		{include file="admin/sidebar.tpl"}