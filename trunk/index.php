<?php
/* MorgOS is a Content Management System written in PHP
 * Copyright (C) 2005-2006 MorgOS
 * This program is free software; you can redistribute it and/or modify 
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * any later version.
 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Library General Public License for more details.
 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. 
*/
/**
 * Here starts the main execution of the program. The main classes are loaded here, and the output is 
 * echoed here.
 *
 * @since 0.2
 * @author Nathan Samson
*/
/**
 * \mainpage MorgOS Documentation
 * \section intro Introduction
 *  MorgOS is a very modular system.
*/
include ('interface/morgos.class.php');

//emulate register_globals off
//This function is copied from php.net
function unregister_GLOBALS () {
   if (!ini_get('register_globals')) {
       return;
   }

   // Might want to change this perhaps to a nicer error
   if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'])) {
       die('GLOBALS overwrite attempt detected');
   }

   // Variables that shouldn't be unset
   $noUnset = array('GLOBALS',  '_GET',
                     '_POST',    '_COOKIE',
                     '_REQUEST', '_SERVER',
                     '_ENV',    '_FILES');

   $input = array_merge($_GET,    $_POST,
                         $_COOKIE, $_SERVER,
                         $_ENV,    $_FILES,
                         isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array());
  
   foreach ($input as $k => $v) {
       if (!in_array($k, $noUnset) && isset($GLOBALS[$k])) {
           unset($GLOBALS[$k]);
       }
   }
}

unregister_GLOBALS ();
startMorgOS ();

function startMorgOS () {
	$morgos = &loadMorgOS ();
	$morgos->run ('viewPage');
	$morgos->shutdown ();
}

?>