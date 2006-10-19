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
 * This is the mailingList class.
 *
 * @since 0.2
 * @author Sam Heijens
*/

class mailingList extends databaseObject 
  {
  function mailingList ($dbModule,$creator) 
    {
	$extraOptions= '';
	$listName = new dbField ('listName', 'varchar (255)'); // veld met naam name, en type varchar(255)
    $basicOptions = new array ('listName'=>$listName;
    $tableName = 'mailingLists';
    $IDName = 'listID';
	base::databaseObject (&$dbModule, $extraOptions, $basicOptions, $tableName, $IDName, &$creator);
	}
	
  function sendmail ($from, $subject, $content) 
	{
	$headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $headers .= 'From:' . $from . "\r\n";
	$headers .= 'Reply-To: ' . $from . "\r\n";
	foreach ($this->getAllMembers as $member) 
	  {
      $emails .= ', ' . $mailingMember->getEmail();
      }
	mail($emails, $subject, $message, $headers);
	}
		
  function getAllMembers()
    {
    $table = $db->getPrefix (). 'mailingMembers'
    $listID = $this->getID ()
    $query = "SELET memberID FROM $tableName WHERE listID='$listID'";
    $q = $db->query ($qurey);
    $members = array();
    while ($row = $db->fetchArray ($q))
      {
      $member = new mailingMember();
      $member->initFromDatabaseID ($row['memberID']);
      $members[] = $member;
      }
    return $members;   
    } 
  } 
?>