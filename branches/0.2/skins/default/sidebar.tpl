<div id="sidebar">
	<div class="box">
		<h3>{t s='Menu'}</h3>
	{foreach from=$MorgOS_Menu item='menuItem'}
		<div class="menu">
			<h4><a href="{$menuItem.Link|xhtml}">{$menuItem.Title}</a></h4>
			{if $menuItem.Childs}
				<ul>
					{foreach from=$menuItem.Childs item='childItem'}
						<li><a href="{$childItem.Link|xhtml}">{$childItem.Title}</a></li>
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
				<p>{t s="Welcome %u" u=$MorgOS_CurUser.Name}</p>
				<p><a href="?action=userLogout">{t s="Logout"}</a></p>
			{else}
				<form action="index.php" method="post">
					<p><label for="userLogin">{t s="Login: "}</label><input type="text" name="login" id="userLogin" /></p>
					<p><label for="userPassword">{t s="Password: "}</label><input type="password" name="password" id="userPassword" /></p>
					<input type="hidden" name="action" value="userLogin" />
					<input type="submit" value="{t s="Login"}"/>
				</form>
				<a href="{$MorgOS_RegisterFormLink}">{t s="Register"}</a>
			{/if}
		</div>
	</div>
	
	{$MorgOS_ExtraSidebar}
</div>