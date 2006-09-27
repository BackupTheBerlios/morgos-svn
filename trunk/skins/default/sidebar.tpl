<div id="sidebar">
	<div class="box">
		<h3>{t s='Menu'}</h3>
	{foreach from=$MorgOS_Menu item='menuItem'}
		<div class="menu">
			<h4><a href="{$menuItem.Link}">{$menuItem.Title}</a></h4>
			{if $menuItem.Childs}
				<ul>
					{foreach from=$menuItem.Childs item='childItem'}
						<li><a href="{$childItem.Link}">{$childItem.Title}</a></li>
					{/foreach}
				</ul>
			{/if}
		</div>
	{/foreach}
	</div>
	
	<div class="box">
		<h3>{t s="User"}</h3>
		<div>
			{if $MorgOS_CurUser}
				{t s="Welcome %u" u="MorgOS_User.Name"}
				<a href="?action=userLogout">{t s="Logout"}</a>
			{else}
				<form action="index.php" method="post">
					<p><label for="userLogin"></label><input type="text" name="login" id="userLogin" /></p>
					<p><label for="userPassword"></label><input type="password" name="password" id="userPassword" /></p>
					<input type="hidden" name="action" value="userLogin" />
					<input type="submit" value="{t s="Login"}"/>
				</form>
				<a href="{$MorgOS_RegisterFormLink}">{t s="Register"}</a>
			{/if}
		</div>
	</div>
</div>