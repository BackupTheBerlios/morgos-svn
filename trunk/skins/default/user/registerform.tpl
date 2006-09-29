{include file="header.tpl"}
	<div id="main">
		<h1>{$MorgOS_CurrentPage_Title}</h1>
		{include file="messages.tpl"}
		<p>{$MorgOS_CurrentPage_Content}</p>
		
		
		<form action="index.php" method="post">
			<p>
				<label for="login">{t s="Login: "}</label>
				<input type="text" name="login" id="login" />
			</p>
			
			<p>
				<label for="email">{t s="Email: "}</label>
				<input type="text" name="email" id="email" />
			</p>
			
			<p>
				<label for="password1">{t s="Password: "}</label>
				<input type="password" name="password1" id="password1" />
			</p>
			
			<p>
				<label for="password2">{t s="Password (repeat): "}</label>
				<input type="password" name="password2" id="password2" />
			</p>
			
			<input type="hidden" value="userRegister" name="action" />
			<input type="submit" value="{t s="Register"}" />
		</form>
	</div>
{include file="sitefooter.tpl"}
{include file="footer.tpl"}