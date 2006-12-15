<form method="post" action="index.php">
	<fieldset>
		<legend>{t s="Password"}</legend>
		
		<p>
			<label for="oldPassword">{t s="Old password: "}</label>
			<input type="password" name="oldPassword" id="oldPassword" />
		</p>
		
		<p>
			<label for="newPassword1">{t s="New password: "}</label>
			<input type="password" name="newPassword1" id="newPassword1" />
		</p>
		
		<p>
			<label for="newPassword2">{t s="New password (repeat): "}</label>
			<input type="password" name="newPassword2" id="newPassord2" />
		</p>
		
		<input type="hidden" name="action" value="userChangePassword" />
		<input type="submit" value="{t s="Change password"}"/>
	</fieldset>
</form>