<?php
/* MorgOS is a Content Management System written in PHP
 * Copyright (C) 2005-2007 MorgOS
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
 * This is the mailingMember class.
 *
 * @since 0.2
 * @author Sam Heijens
*/

class mailingMember extends databaseObject 
  {
  function mailingMember ($dbModule) 
    {
	$extraOptions= '';
	$listName = new dbField ('userName', 'varchar (255)'); 
	$emailAdres = new dbField ('emailAdres', 'varchar (255)');
	$groupID = new dbField ('ListID', 'int (11)');
    $basicOptions = array ('listName'=>$listName, 'emailAdres'=>$emailAdres, 'groupID'=>$groupID);
    $tableName = 'mailingMembers';
    $IDName = 'memberID';
    $creator = null;
	base::databaseObject ($dbModule, $extraOptions, $basicOptions, $tableName, $IDName, $creator);
	}

  function getName() 
    {
	return $this->getOption ('userName');
    }
		
  function getEmail()
	{
	return $this->getOption ('emailAdres');
 	}
  }
?>