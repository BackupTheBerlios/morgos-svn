<html>
	<head>
		<title>{$MorgOS_CurrentAdminPage->getGenericName()}</title>
		
		<!-- CSS FILES -->
		<link rel="stylesheet" type="text/css" href="{$SkinPath}/styles/default.css" />
		<!--[IF IE lte 6]>
			<link rel="stylesheet" ref="{$SkinPath}/styles/default.hacks.IE6-55-5.css" />
		<![endif]-->
	</head>
	<body>
		<div>
			<div id="header">
				MorgOS Admin
			</div>
			{include file="admin/nav.tpl"}
			{include file="admin/sidebar.tpl"}