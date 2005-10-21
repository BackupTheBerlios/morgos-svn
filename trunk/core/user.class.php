<?php
class useroptions {
function login()
{

}

function logout()
{
}

function insertuser($name, $email, $pass)
	{
  	  mysql_query("INSERT INTO users (name, email, pass) VALUES ($name, $email, $pass)");
	}

function getuser($id)
	{
	  $query = mysql_query("SELECT * FROM users WHERE id = $id");
	  $getuser = mysql_fetch_array($query);
	}	

function updateuser($name, $email , $pass, $id)
	{
	  mysql_query("UPDATE users SET name = $name, email = $email, pass = $pass WHERE id = $id LIMIT 1");
	}
}

?>