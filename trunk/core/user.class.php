<?php
class user 
	{
	function login($passp,$username)
		{
		$query = $genDB->_query("SELECT username, pass FROM users WHERE name = $username");
		while ($i = $genDB->_fetch_array($query))
			{
			$passd = $i['pass'];
			}
		if ($passd == $passp)
			{
			$_SESSION['username'] = $username;
			$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
			$_SESSION['pass'] = $pass;
			session_register('username');
			session_register('ip');
			session_register('pass');
			}
		}
	function logout()
		{
		session_start();
		unset ($_SESSION['username']);
		unset ($_SESSION['pass']);
		unset ($_SESSION['ip']);
		}
	function insertuser($username, $email, $pass)
		{
		$exist = $genDB->_query("SELECT name FROM users");
		while ($ii = $genDB->_fetch_array($exist))
			{
			$usernamedb = $ii['name'];
			}
		if ($username == $username)
			{
			echo 'User already exists';
			}	
		else 
			{
			$genDB->_query("INSERT INTO users (username, email, pass) VALUES ($username, $email, $pass)");
			}
		}
	function getuser($id)
		{
		$query = $genDB->_query("SELECT * FROM users WHERE id = $id");
		return $genDB->_fetch_array($query);
		}	
	function updateuser($username, $email , $pass, $id)
		{
		$genDB->_query("UPDATE users SET name = $username, email = $email, pass = $pass WHERE id = $id LIMIT 1");
		}
	}
?>