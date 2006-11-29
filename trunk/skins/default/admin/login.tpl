<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8;" />
		<link rel="stylesheet" type="text/css" href="{$SkinPath}/styles/grayish/admin.css" />
		<title>{t s='Login'}</title>
	</head>
	<body>
		<div id="loginBox">
			<img src="{$SkinPath}/images/login.png" alt="{$t->t ('Login')}" />
			<h2>{t s='Login'}</h2>

			{include file="admin/usermessages.tpl"}			
			
			<form method="post" action="index.php">
				{morgos_form_action a="adminLogin"}
				<p><label for="login">{t s='Login: '}</label>
					<input type="text" name="adminLogin" id="login" />
				</p>
				<p><label for="password">{t s='Password: '}</label>
					<input type="password" name="adminPassword" id="password" />
				</p>
				<input type="submit" value="{t s='Login'}" />
			</form>
		</div>
	</body>
</html>