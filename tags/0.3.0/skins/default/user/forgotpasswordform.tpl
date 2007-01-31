<form action="index.php" method="POST">
	<p>
		<label for="userAccount">{t s="Username: "}</label>
		<input type="text" name="userAccount" id="userAccount" />
	</p>
	
	<em>OR</em>
	
	<p>
		<label for="accountEmail">{t s="Email: "}</label>
		<input type="text" name="accountEmail" id="accountEmail" />
	</p>
	
	<input type="hidden" name="action" value="userForgotPassword"/>
	<input type="submit" value="{t s="Send password"}" />
</form>