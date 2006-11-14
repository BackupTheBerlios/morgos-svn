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
 * @ingroup user core
 * @since 0.2
 * @author Nathan Samson
*/

/**
 * A class that represents a translatedgroup
 *
 * @ingroup user core
 * @since 0.2
 * @author Nathan Samson
*/
class UserTranslatedGroup extends DBTableObject {

	/**
	 * Constructor.
	 *
	 * @param $db (dbModule)
	 * @param $creator (object)
	 * @param $extraFields (object dbField array)
	 * @param $extraJoins (object genericJoinField array)
	*/
	function UserTranslatedGroup ($db, &$creator, $extraFields = array (), $extraJoins = array ()) {
		$name = new dbField ('name', DB_TYPE_STRING, 255);
		$description = new dbField ('description', DB_TYPE_TEXT);
		$groupID = new dbField ('groupID', DB_TYPE_INT);
		$groupID->canBeNull = true;	
		$lCode = new dbField ('languageCode', DB_TYPE_STRING, 5);	
	
		parent::DBTableObject ($db, array ($name, $description, $groupID, $lCode), 'translatedGroups', 'translatedGroupID', $creator, $extraFields);
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
			return new Error ('DATABASEOBJECT_SQL_INJECTION_FAILED',__FILE__,__LINE__);
		}
		$languageCode = $this->_db->escapeString ($lCode);
		$fullTableName = $this->getFullTableName ();
		$sql = "SELECT * FROM $fullTableName WHERE $groupID='$groupID' AND languageCode='$languageCode'";
		$q = $this->_db->query ($sql);
		if (! isError ($q)) {
			if ($this->_db->numRows ($q) == 1) {
				$row = $this->_db->fetchArray ($q);
				$this->initFromArray ($row);
				$this->setField ('ID', $row['translatedGroupID']);
			} else {
				return new Error ('TRANSLATEDGROUP_CANTFIND_GROUP', $groupID, $languageCode);
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
	function getName () {return $this->getFieldValue ('name');}
	/**
	 * Returns the description.
	 * @public
	 * @return (string)
	*/
	function getDescription () {return $this->getFieldValue ('description');}
	/**
	 * Returns the language code.
	 * @public
	 * @return (string)
	*/
	function getLanguageCode () {return $this->getFieldValue ('languageCode');}
	/**
	 * Returns the groupID.
	 * @public
	 * @return (int)
	*/
	function getGroupID () {return $this->getFieldValue ('groupID');}
	
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
