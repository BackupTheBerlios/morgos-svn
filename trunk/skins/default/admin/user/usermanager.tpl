{if $MorgOS_Current_Admins}
<h2>{t s="Administrators"}</h2>
<table>
	<tr>
		<th>{t s="Username"}</th>
		<th>{t s="Make normal user"}</th>
		<th>{t s="Delete"}</th>
	</tr>
	{foreach from=$MorgOS_Current_Admins item='user'}
		<tr>
			<td>{$user.Login}</td>
			<td>
				{if !$user.IsCurrent}
				<form action="index.php" method="post">
					<input type="hidden" name="userID" value="{$user.ID}" />
					<input type="hidden" name="action" value="adminMakeUserNormal" />
					<input type="submit" value="{t s="Normal user"}" />
				</form>
				{/if}
			</td>
			<td>
				{if !$user.IsCurrent}
				<a href="index.php?action=adminUserDelete&amp;userID={$user.ID}">
					<img src="{$SkinPath}/images/icons/delete.png" alt="{t s="Delete"}"/>
				</a>
				{/if}
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
		<th>{t s="Delete"}</th>
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
			<td>
				<a href="index.php?action=adminUserDelete&amp;userID={$user.ID}">
					<img src="{$SkinPath}/images/icons/delete.png" alt="{t s="Delete"}"/>
				</a>
			</td>
		</tr>
	{/foreach}
</table>
{/if}

<h2>{t s="New user"}</h2>
{include file="user/registerform.tpl"}