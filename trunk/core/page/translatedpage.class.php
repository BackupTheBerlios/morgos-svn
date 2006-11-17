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
/** \file translatedpage.class.php
 * A translatedpage class.
 *
 * @ingroup page core
 * @since 0.2
 * @author Nathan Samson
*/

/**
 * A class that represents a translatedpage
 *
 * @ingroup page core
 * @since 0.2
 * @author Nathan Samson
*/
class TranslatedPage extends DBTableObject {

	/**
	 * Constructor
	 *
	 * @param $db (dbModule)
	 * @param $parent (object) the creator of this object (a pagemanager object)
	 * @param $extraFields (object dbField array)
	 * @param $extraJoins (object dbGenericJoinField array)
	*/
	function TranslatedPage ($db, &$parent, $extraFields = array (), $extraJoins = array ()) {
		$translatedTitle = new dbField ('translatedTitle', DB_TYPE_STRING, 255);
		$translatedNavTitle = new dbField ('translatedNavTitle', DB_TYPE_STRING, 255);
		$translatedNavTitle->canBeNull = true;
		$translatedContent = new dbField ('translatedContent', DB_TYPE_STRING, 255);
		$translatedContent->canBeNull = true;		
		$pageID = new dbField ('pageID', DB_TYPE_INT, 11);
		$pageID->canBeNull = true;
		$languageCode = new dbField ('languageCode', DB_TYPE_STRING, 5);	
		$ID = new dbField ('translatedPageID', DB_TYPE_INT, 11);
		
		parent::DBTableObject ($db, array ('translatedPageID'=>$ID, 'translatedTitle'=>$translatedTitle, 'translatedNavTitle'=>$translatedNavTitle, 'translatedContent'=>$translatedContent,'pageID'=>$pageID, 'languageCode'=>$languageCode), 'translatedPages', 'translatedPageID', $parent, $extraFields);
	}
	
	/**
	 * Initializes the object for a page and translation.
	 *
	 * @param $pageID (int)
	 * @param $languageCode (string)
	 * @public
	*/
	function initFromDatabasePageIDandLanguageCode ($pageID, $languageCode) {
		if (! is_numeric ($pageID)) {
			return new Error ('DATABASEOBJECT_SQL_INJECTION_ATTACK_FAILED', __FILE__, __LINE__);
		}
		$languageCode = $this->_db->escapeString ($languageCode);
		$fTN = $this->getFullTableName ();
		$sql = "SELECT * FROM $fTN WHERE $pageID='$pageID' AND languageCode='$languageCode'";
		$q = $this->_db->query ($sql);
		if (! isError ($q)) {
			if ($this->_db->numRows ($q) == 1) {
				$row = $this->_db->fetchArray ($q);
				$this->initFromArray ($row);
				$this->setField ('ID', $row['translatedPageID']);
			} else {
				return new Error ('ERROR_TRANSLATEDPAGE_CANTFIND_PAGE', $pageID, $languageCode);
			}
		} else {
			return $q;
		}
	}	
	
	/**
	 * Returns the title of the page
	 * @public
	 * @return (string)
	*/
	function getTitle () {return $this->getFieldValue ('translatedTitle');}
	
	/**
	 * Returns the navigation title of the page
	 * @public
	 * @return (string)
	*/
	function getNavTitle () {
		if ($this->getFieldValue ('translatedNavTitle')) {
			return $this->getFieldValue ('translatedNavTitle');
		} else {
			return $this->getFieldValue ('translatedTitle');
		}
	}
	
	/**
	 * Returns the content
	 * @public
	 * @return (string)
	*/
	function getContent () {return $this->getFieldValue ('translatedContent');}
	/**
	 * Returns the pageID
	 * @public
	 * @return (int)
	*/
	function getPageID () {return $this->getFieldValue ('pageID');}
	/**
	 * Returns the languageCode
	 * @public
	 * @returns (string)
	*/	
	function getLanguageCode () {return $this->getFieldValue ('languageCode');}
	
	/**
	 * Returns the original page where this is a translation for.
	 * @public
	 * @returns (object page)
	*/
	function getPage () {
		$par = $this->getParent ();
		$page = $par->newTranslatedPage ();
		$page->initFromDatabaseID ($this->getPageID ());
	}

}
?>