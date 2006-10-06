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
class translatedPage extends databaseObject {

	/**
	 * Constructor
	 *
	 * @param $db (dbModule)
	 * @param $extraOptions (object dbField array)
	 * @param $parent (object) the creator of this object (a pagemanager object)
	*/
	function translatedPage ($db, $extraOptions, &$parent) {
		$translatedTitle = new dbField ('translatedTitle', 'varchar(255)');
		$translatedNavTitle = new dbField ('translatedNavTitle', 'varchar(255)');
		$translatedNavTitle->canBeNull = true;
		$translatedContent = new dbField ('translatedContent', 'varchar(255)');
		$translatedContent->canBeNull = true;		
		$pageID = new dbField ('pageID', 'int(11)');
		$pageID->canBeNull = true;
		$languageCode = new dbField ('languageCode', 'varchar(5)');		
		
		parent::databaseObject ($db, $extraOptions, array ('translatedTitle'=>$translatedTitle, 'translatedNavTitle'=>$translatedNavTitle, 'translatedContent'=>$translatedContent,'pageID'=>$pageID, 'languageCode'=>$languageCode), 'translatedPages', 'translatedPageID', $parent);
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
		$languageCode = $this->db->escapeString ($languageCode);
		$fTN = $this->getFullTableName ();
		$sql = "SELECT * FROM $fTN WHERE $pageID='$pageID' AND languageCode='$languageCode'";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			if ($this->db->numRows ($q) == 1) {
				$row = $this->db->fetchArray ($q);
				$this->initFromArray ($row);
				$this->setOption ('ID', $row['translatedPageID']);
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
	function getTitle () {return $this->getOption ('translatedTitle');}
	
	/**
	 * Returns the navigation title of the page
	 * @public
	 * @return (string)
	*/
	function getNavTitle () {
		if ($this->getOption ('translatedNavTitle')) {
			return $this->getOption ('translatedNavTitle');
		} else {
			return $this->getOption ('translatedTitle');
		}
	}
	
	/**
	 * Returns the content
	 * @public
	 * @return (string)
	*/
	function getContent () {return $this->getOption ('translatedContent');}
	/**
	 * Returns the pageID
	 * @public
	 * @return (int)
	*/
	function getPageID () {return $this->getOption ('pageID');}
	/**
	 * Returns the languageCode
	 * @public
	 * @returns (string)
	*/	
	function getLanguageCode () {return $this->getOption ('languageCode');}
	
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