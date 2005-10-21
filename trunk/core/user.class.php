<?php


function insertuser()
	{
  	  mysql_query("INSERT INTO users (name, email, pass) VALUES ($name, $email, $pass)");
	}



?>