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
/** \file usertranslatedgroups.class.php
 * File that take care of one translated group
 *
 * @since 0.2
 * @author Nathan Samson
*/

class translatedGroup extends databaseObject {

	/**
	 * Constructor.
	 *
	 * @param $db (dbModule)
	 * @param $allExtraOptions (object dbField array)
	 * @param $creator (object)
	*/
	function translatedGroup ($db, $allExtraOptions, &$creator) {
		$name = new dbField ();
		$name->name = 'name';
		$name->type = 'varchar (255)';
		
		$description = new dbField ();
		$description->name = 'description';
		$description->type = 'text';		
		
		$groupID = new dbField ();
		$groupID->name = 'groupID';
		$groupID->type = 'int (11)';
		$groupID->canBeNull = true;	
		
		$lCode = new dbField ('languageCode', 'varchar(5)');		
	
		parent::databaseObject ($db, $allExtraOptions, array ('name'=>$name, 'description'=>$description, 'groupID'=>$groupID, 'languageCode'=>$lCode), 'translatedGroups', 'translatedGroupID', $creator);
	}

	/**
	 * Initializes the object.
	 *
	 * @param $groupID (int)
	 * @param $lCode (string) the language code
	 * @public
	*/
	function initFromDatabaseGroupIDandLanguageCode ($groupID, $lCode) {
		if (! is_numeric ($groupID)) {
			return "ERROR_DATABASEOBJECT_SQL_INJECTION_FAILED ".__FILE__." ".__LINE__;
		}
		$languageCode = $this->db->escapeString ($lCode);
		$sql = "SELECT * FROM {$this->getFullTableName ()} WHERE $groupID='$groupID' AND languageCode='$languageCode'";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			if ($this->db->numRows ($q) == 1) {
				$row = $this->db->fetchArray ($q);
				$this->initFromArray ($row);
				$this->setOption ('ID', $row['translatedGroupID']);
			} else {
				return "ERROR_TRANSLATEDGROUP_CANTFIND_GROUP $groupID $languageCode";
			}
		} else {
			return $q;
		}
	}

	/**
	 * Returns the name.
	 * @public
	 * @return (string)
	*/
	function getName () {return $this->getOption ('name');}
	/**
	 * Returns the description.
	 * @public
	 * @return (string)
	*/
	function getDescription () {return $this->getOption ('description');}
	/**
	 * Returns the language code.
	 * @public
	 * @return (string)
	*/
	function getLanguageCode () {return $this->getOption ('languageCode');}
	/**
	 * Returns the groupID.
	 * @public
	 * @return (int)
	*/
	function getGroupID () {return $this->getOption ('groupID');}
	
	/**
	 * Returns the group
	 * @public
	 * @return (object group)
	*/
	function getGroup () {
		$c = $this->getCreator ();
		$p = $c->newGroup ();
		$p->initFromDatabaseID ($this->getGroupID ());
		return $p;
	}
}

?>
