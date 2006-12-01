{include file='admin/header.tpl'}
<div>
<div id="login">
{include file='admin/usermessages.tpl'}
<form action="index.php" method="post">
<input type="hidden" name="action" value="adminLogin">
<input type="text" name="adminLogin" value="Username">
<input type="password" name="adminPassword" value="password">
<input type="submit" value="Log In">
</form>
</div>
</div>
{include file='admin/footer.tpl'}