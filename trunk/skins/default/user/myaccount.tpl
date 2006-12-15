<form method="post" action="index.php">
	<fieldset>
		<legend>{t s="Account settings"}</legend>
		
		<p>
			<label for="newEmail">{t s="Email: "}</label>
			<input type="text" name="newEmail" id="newEmail" 
				  value="{$MorgOS_User_MyAccount_OldEmail}" />
		</p>
		
		<input type="hidden" name="action" value="userChangeAccount" />
		<input type="submit" value="{t s="Change account"}" />
	</fieldset>
</form>

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
		<input type="submit" value="{t s="Change password"}" />
	</fieldset>
</form>