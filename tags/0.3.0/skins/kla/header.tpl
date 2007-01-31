<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>{$MorgOS_CurrentPage_Title}</title>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<meta http-equiv="content-language" content="nl" />
		<meta name="author" content="Nathan Samson" />
		<meta name="description" content="De schoolwebsite van het KLA" />
		<meta name="keywords" content="KLA,koninklijk lyceum antwerpen" />
		<link rel="stylesheet" type="text/css" href="{$SkinPath}/styles/standaard/basis.css" />
	  	<link rel="stylesheet" type="text/css" href="{$SkinPath}/styles/standaard/menu.css" />
	  	
	  	<link rel="alternate stylesheet" type="text/css" href="{$SkinPath}/styles/leerlingenraad/basis.css" title="Leerlingenraad"/>
	  	<link rel="alternate stylesheet" type="text/css" href="{$SkinPath}/styles/leerlingenraad/menu.css" title="Leerlingenraad"/>
	  	
	  	<script type="text/javascript" src="{$SkinPath}/styles/navfixes.js"></script>
	  	<!--[if IE]>
			<link href="ie_win.css" rel="stylesheet" type="text/css" />
		<![endif]-->
		{$MorgOS_ExtraHead}
	</head>
	<div class="header">
		<div id="nav">
			<ol>
				{foreach from=$MorgOS_RootMenu item='menuItem'}
					<li>
						<a href="{$menuItem.Link|xhtml}">{$menuItem.Title}</a>
						{if $menuItem.Childs}
							<ol>
								{foreach from=$menuItem.Childs item='subMenuItem'}
									<li>
										<a href="{$subMenuItem.Link|xhtml}">{$subMenuItem.Title}</a>
									</li>
								{/foreach}
							</ol>
						{/if}
					</li>
				{/foreach}
			</ol>
		</div>
	</div>
	<div class="content">
	{include file="admin/messages.tpl"}
	