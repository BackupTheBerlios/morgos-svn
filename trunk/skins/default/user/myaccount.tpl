<h3>{t s="Account"}</h3>
<form method="post" action="index.php">
	<fieldset>
		<legend>{t s="Interface settings"}</legend>
		
		<p>
			<label for="newSkin">{t s="Skin: "}</label>
			<select name="newSkin" id="newSkin">
				{foreach from=$MorgOS_User_MyAccount_AvailableSkins item='Skin'}
					{if $MorgOS_User_MyAccount_CurrentSkin == $Skin.ID}
						<option selected="selected" 
							   value="{$Skin.ID}">{$Skin.Name}
						</option>
					{else}
						<option value="{$Skin.ID}">{$Skin.Name}</option>
					{/if}
				{/foreach}
			</select>
			
		</p>
		
		<p>
			<label for="newLanguage">{t s="Language: "}</label>
			<select name="newContentLanguage" id="newLanguage">
				{foreach from=$MorgOS_User_MyAccount_AvailableContentLanguages item='Lang'}
					{if $MorgOS_User_MyAccount_CurrentContentLanguage == $Lang}
						<option selected="selected">{$Lang}</option>
					{else}
						<option>{$Lang}</option>
					{/if}
				{/foreach}
			</select>
			
		</p>
	</fieldset>

	<fieldset>
		<legend>{t s="Account settings"}</legend>
		
		<p>
			<label for="newEmail">{t s="Email: "}</label>
			<input type="text" name="newEmail" id="newEmail" 
				  value="{$MorgOS_User_MyAccount_OldEmail}" />
		</p>
		
		<input type="hidden" name="action" value="userChangeAccount" />
	</fieldset>
	<input type="submit" value="{t s="Change account"}" />
</form>

<h3>{t s="Change Password"}</h3>
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