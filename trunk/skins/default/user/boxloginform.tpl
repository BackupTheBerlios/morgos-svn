<form action="index.php" method="post">
	<p><label for="userLogin">{t s="Login: "}</label>
		<input type="text" name="login" id="userLogin" />
	</p>
	<p><label for="userPassword">{t s="Password: "}</label>
		<input type="password" name="password" id="userPassword" />
	</p>
	
	<input type="hidden" name="action" value="userLogin" />
	<input type="submit" value="{t s="Login"}"/>
</form>
<a href="index.php?action=userRegisterForm">{t s="Register"}</a>