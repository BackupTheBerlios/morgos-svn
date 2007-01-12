<form action="index.php" method="post">
<input type="hidden" name="action" value="userLogin">
Login: <input type="text" name="login" value="Username"> <br />
Password: <input type="password" name="password"><br />
<input type="submit">
</form>
<a href="index.php?action=userRegisterForm">{t s="Register"}</a> <br />
<a href="index.php?action=userForgotPasswordForm">{t s="Forgot Password"}</a> <br />