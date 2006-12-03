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
/** \file page.class.php
 * Manager of the pages.
 *
 * @ingroup page core
 * @since 0.2
 * @author Nathan Samson
*/

/**
 * A class that represents a page
 *
 * @ingroup page core
 * @since 0.2
 * @author Nathan Samson
*/
class Page extends DBTableObject {

	/**
	 * Constructor
	 *
	 * @param $db (object dbModule)
	 * @param $parent (object) The creator
	 * @param $allEFields (dbField array) all extra fields
	 * @param $allEJoins (dbGenericJoin array) all extra joins
	*/	
	function Page ($db, &$parent, $allEFields = array (), $allEJoins = array ()) {
		$name = new dbField ('name', DB_TYPE_STRING, 255);
		$parentPageID = new dbField ('parent_page_id', DB_TYPE_INT, 11);
		$placeInMenu = new dbField ('place_in_menu', DB_TYPE_INT, 4);
		$placeInMenu->canBeNull = true;
		$action = new dbField ('action', DB_TYPE_STRING, 255);
		$action->canBeNull = true;
		$pluginID = new dbField ('plugin_id', DB_TYPE_STRING, 36);
		$pluginID->canBeNull = true;
		$ID = new dbField ('page_id', DB_TYPE_INT, 11);
		
		$translatedJoin = new oneToMultipleJoinField ('translatedPages', 
				$db->getPrefix ().'translatedPages', 'page_id', $ID);
				
		$childJoin = new oneToMultipleJoinField ('childPages', 
				$db->getPrefix ().'pages', 'parent_page_id', $ID);
		
				
		parent::DBTableObject ($db, array ($ID, $name, $parentPageID, 
			$placeInMenu, $action, $pluginID), 
			'pages', 'page_id', $parent, $allEFields, 
			array ($translatedJoin, $childJoin));
	}

	/**
	 * Initializes the page from a generic name
	 *
	 * @param $name (string) The generic name
	 * @public
	*/
	function initFromName ($name) {
		$fullTableName = $this->getFullTableName ();
		$name = $this->_db->escapeString ($name);
		$sql = "SELECT * FROM $fullTableName WHERE name='$name'";
		$q = $this->_db->query ($sql);
		if (! isError ($q)) {
			if ($this->_db->numRows ($q) == 1) {
				$row = $this->_db->fetchArray ($q);
				$this->initFromArray ($row);
				$this->setField ('ID', $row[$this->getIDName ()]);
			} else {
				return new Error ('PAGE_NAME_DOESNT_EXISTS', $name);
			}
		} else {
			return $q;
		}
	}
	
	/**
	 * Returns the generic name of the page
	 *
	 * @public
	 * @return (string)
	*/
	function getName () {return $this->getFieldValue ('name');}
	
	/**
	 * Returns the ID of the parentPage. 0 if it is a root element.
	 *
	 * @public
	 * @return (int)
	*/
	function getParentPageID () {return $this->getFieldValue ('parent_page_id');}
	
	/**
	 * Returns the place in the menu
	 * 
	 * @public
	 * @return (int)
	*/
	function getPlaceInMenu () {return $this->getFieldValue ('place_in_menu');}

	/**
	 * If the page needs a special action returns it.
	 * @public
	 * @return (string)
	*/
	function getAction () {return $this->getFieldValue ('action');}
	/**
	 * Returns the link for the page.
	 * @public
	 * @return (string)
	*/
	function getLink () {
		$baseLink = 'index.php';
		if ($this->getAction ()) {
			return $baseLink .= '?action='.$this->getAction ();
		}
		elseif ($this->isAdminPage ()) {
			return $baseLink .= '?action=admin&pageID='.$this->getID ();
		} else {
			return $baseLink .= '?action=viewPage&pageID='.$this->getID ();
		}
	}
	/**
	 * Returns the pluginID for the page
	 * @public
	 * @return (string)
	*/
	function getPluginID () {return $this->getFieldValue ('plugin_id');}
	
	/**
	 * Returns of the page is in the admin site
	 * @public
	 * @return (bool)
	*/
	function isAdminPage () {
		if ($this->getName () == 'admin') {
			return true;
		} else {
			$parentPage = $this->getParentPage ();
			if ($parentPage !== null) {
				return $parentPage->isAdminPage ();
			} else {
				return false;
			}
		}
	}

	function isRootPage () {
		return $this->getParentPageID () == 0;
	}
	
	/**
	 * Returns the parentPage.
	 *
	 * @public
	 * @return (object)
	*/
	function getParentPage () {
		if ($this->isRootPage () == false) {
			$parent = $this->getCreator ();
			$parentPage = $parent->newPage ();
			$a = $parentPage->initFromDatabaseID ($this->getParentPageID ());
			return $parentPage;
		} else {
			return null;
		}
	}
	
	/**
	 * Returns a translated object that is inited.
	 *
	 * @param $languageCode (string)
	 * @public
	 * @return (object translatedPage) 
	*/
	function getTranslation ($languageCode) {
		$trans = $this->getAllChildTables ('translatedPages', 'language_code', ORDER_ASC, 
				array (new WhereClause ('language_code', $languageCode, '=')));
		if ((count ($trans) >= 1) and (! isError ($trans))) {
			$tPageArray = $trans[0]; /*Normally I should get only one*/
			$creator = $this->getCreator ();
			$tPage = $creator->newTranslatedPage ();
			$tPage->initFromArray ($tPageArray);
			return $tPage;
		}
	
		return new Error ('PAGE_TRANSLATION_DOESNT_EXIST', $languageCode);
	}
	
	function getAllTranslations () {
		$tPages = array ();
		foreach ($this->getAllChildTables ('translatedPages', 'language_code', ORDER_ASC) 
				as $tPageArray) {
			$creator = $this->getCreator ();
			$tPage = $creator->newTranslatedPage ();
			$tPage->initFromArray ($tPageArray);
			$tPages[] = $tPage;
		}
		return $tPages;
	}
	
	function getAllTranslationCodes () {
		$tCodes = array ();
		$c = $this->getAllChildTables ('translatedPages', 'language_code', ORDER_ASC, 
			array (), 'language_code');
		if (! isError ($c)) {
			foreach ($c as $tPageArray) {
				$tCodes[] = $tPageArray['language_code'];
			}
			return $tCodes;
		} else {	
			return $c;
		}
	}
	
	function addTranslation (&$translation) {
		if (! $this->translationExists ($translation->getLanguageCode ())) {
			$a['page_id'] = $this->getID ();
			$translation->updateFromArray ($a);
			$a = $translation->addToDatabase ();
		} else {
			return new Error ('PAGE_TRANSLATION_EXISTS', 
				$translation->getLanguageCode ());
		}
	}
	
	function removeTranslation ($translation) {
		if ($this->translationExists ($translation->getLanguageCode ())) {
			return $translation->removeFromDatabase ();
		} else {
			return new Error ('PAGE_TRANSLATION_DOESNT_EXIST', 
				$translation->getLanguageCode ());
		}
	}
	
	function translationExists ($languageCode) {
		if (in_array ($languageCode, $this->getAllTranslationCodes ())) {
			return true;
		} else {
			return false;
		}
	}
	
	function getAllChilds () {
		$cPages = array ();
		foreach ($this->getAllChildTables ('childPages', 'placeInMenu', ORDER_ASC) 
				as $cPageArray) {
			$creator = $this->getCreator ();
			$cPage = $creator->newPage ();
			$cPage->initFromArray ($cPageArray);
			$cPages[] = $tPage;
		}
		return $cPages;
	}
	
	function getMaxPlaceInMenu () {
		$sql = "SELECT MAX(place_in_menu) AS maxplace FROM ".$this->getFullTableName ()." 
				WHERE parent_page_id='". $this->getID ()."'";
		$q = $this->_db->query ($sql);
		if (! isError ($q)) {
			$row = $this->_db->fetchArray ($q);
			return $row['maxplace']+1;
		} else {
			return $a;
		}
	}
	
	function initEmpty () {
		parent::initEmpty ();
		$this->setField ('place_in_menu', -1);
	}
}
?>
