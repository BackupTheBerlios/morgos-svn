{include file="admin/header.tpl"}
	<h1>{$MorgOS_CurrentAdminPage.Title}</h1>
	<p>{$MorgOS_CurrentAdminPage.Content}</p>
	

	{if $MorgOS_Current_Admins}
	<h2>{t s="Administrators"}</h2>
	<table>
		<tr>
			<th>{t s="Username"}</th>
			<th>{t s="Make normal user"}</th>
		</tr>
		{foreach from=$MorgOS_Current_Admins item='user'}
			<tr>
				<td>{$user.Login}</td>
				<td>
					<form action="index.php" method="post">
						<input type="hidden" name="userID" value="{$user.ID}" />
						<input type="hidden" name="action" value="adminMakeUserNormal" />
						<input type="submit" value="{t s="Normal user"}" />
					</form>
				</td>
			</tr>
		{/foreach}
	</table>
	{/if}
	
	{if $MorgOS_All_Users}
	<h2>{t s="Users"}</h2>
	<table>
		<tr>
			<th>{t s="Username"}</th>
			<th>{t s="Make administrator"}</th>
		</tr>
		{foreach from=$MorgOS_All_Users item='user'}
			<tr>
				<td>{$user.Login}</td>
				<td>
					<form action="index.php" method="post">
						<input type="hidden" name="userID" value="{$user.ID}" />
						<input type="hidden" name="action" value="adminMakeUserAdmin" />
						<input type="submit" value="{t s="Administrator"}" />
					</form>
				</td>
			</tr>
		{/foreach}
	</table>
	{/if}
	
	<h2>{t s="New user"}</h2>
	{include file="user/registerform.tpl"}
{include file="admin/footer.tpl"}