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
 * @since 0.2
 * @author Nathan Samson
*/

class page extends databaseObject {

	/**
	 * Constructor
	 *
	 * @param $db (object dbModule)
	 * @param $allOptions (null array)
	 * @param $parent (object) The creator
	*/	
	function page ($db, $allOptions, &$parent) {
		$name = new dbField ('name', 'varchar (255)');
		$parentPageID = new dbField ('parentPageID', 'int(11)');
		$placeInMenu = new dbField ('placeInMenu', 'int(4)');
		$placeInMenu->canBeNull = true;
		$action = new dbField ('action', 'varchar(255)');
		$action->canBeNull = true;
		$pluginID = new dbField ('pluginID', 'varchar(36)');
		$pluginID->canBeNull = true;
				
		parent::databaseObject ($db, $allOptions, array ('name'=>$name, 'parentPageID'=>$parentPageID, 'placeInMenu'=>$placeInMenu, 'action'=>$action, 'pluginID'=>$pluginID), 'pages', 'pageID', $parent);
	}

	/**
	 * Initializes the page from a generic name
	 *
	 * @param $genericName (string) The generic name
	 * @public
	*/
	function initFromName ($name) {
		$fullTableName = $this->getFullTableName ();
		$name = $this->db->escapeString ($name);
		$sql = "SELECT * FROM $fullTableName WHERE name='$name'";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			if ($this->db->numRows ($q) == 1) {
				$row = $this->db->fetchArray ($q);
				$this->initFromArray ($row);
				$this->setOption ('ID', $row[$this->getIDName ()]);
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
	function getName () {return $this->getOption ('name');}
	
	/**
	 * Returns the ID of the parentPage. 0 if it is a root element.
	 *
	 * @public
	 * @return (int)
	*/
	function getParentPageID () {return $this->getOption ('parentPageID');}
	
	/**
	 * Returns the place in the menu
	 * 
	 * @public
	 * @return (int)
	*/
	function getPlaceInMenu () {return $this->getOption ('placeInMenu');}

	/**
	 * If the page needs a special action returns it.
	 * @public
	 * @return (string)
	*/
	function getAction () {return $this->getOption ('action');}
	/**
	 * Returns the link for the page.
	 * @public
	 * @return (string)
	*/
	function getLink () {
		$baseLink = 'index.php';
		if ($this->getAction ()) {
			return $baseLink .= '?action='.$this->getAction ();
		} elseif ($this->isAdminPage ()) {
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
	function getPluginID () {return $this->getOption ('pluginID');}
	
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
		$languageCode = $this->db->escapeString ($languageCode);
		$fullTranslationTableName = $this->db->getPrefix ().'translatedPages';
		$ID = $this->getID ();
		$sql = "SELECT translatedPageID FROM $fullTranslationTableName WHERE pageID='$ID' AND languageCode='$languageCode'";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			if ($this->db->numRows ($q) == 1) {
				$row = $this->db->fetchArray ($q);
				$c = $this->getCreator ();
				$tPage = $c->newTranslatedPage ();
				$tPage->initFromDatabaseID ($row['translatedPageID']);
				return $tPage;
			} else {
				if (strlen ($languageCode) > 2) {
					$firstLang = substr ($languageCode, 0, 2);
					return $this->getTranslation ($firstLang);
				} else {
					return new Error ('PAGE_TRANSLATION_NOT_FOUND');
				}
			} 
			return $tPage;
		} else {
			return $q;
		}
	}
	
	function getAllTranslations () {
		$fullTranslationTableName = $this->db->getPrefix ().'translatedPages';
		$ID = $this->getID ();
		$sql = "SELECT languageCode FROM $fullTranslationTableName WHERE pageID='$ID' ORDER BY languageCode ASC";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			$lCodes = array ();
			while ($row = $this->db->fetchArray ($q)) {
				$lCodes[] = $row['languageCode'];
			}
			return $lCodes;
		} else {
			return $q;
		}
	}
	
	function addTranslation (&$translation) {
		if (! $this->translationExists ($translation->getLanguageCode ())) {
			$a['pageID'] = $this->getID ();
			$translation->updateFromArray ($a);
			$translation->addToDatabase ();
		} else {
			return new Error ('PAGE_TRANSLATION_EXISTS', $translation->getLanguageCode ());
		}
	}
	
	function removeTranslation ($translation) {
		if ($this->translationExists ($translation->getLanguageCode ())) {
			return $translation->removeFromDatabase ();
		} else {
			return new Error ('PAGE_TRANSLATION_DOESNT_EXISTS', $translation->getLanguageCode ());
		}
	}
	
	function translationExists ($languageCode) {
		if (in_array ($languageCode, $this->getAllTranslations ())) {
			return true;
		} else {
			return false;
		}
	}
	
	function getAllChilds () {
		$fTN = $this->getFullTableName ();
		$ID = $this->getID ();
		$sql = "SELECT pageID FROM $fTN WHERE parentPageID='$ID'";
		$q = $this->db->query ($sql);
		$childPages = array ();
		while ($row = $this->db->fetchArray ($q)) {
			$c = $this->getCreator ();
			$childPage = $c->newPage ();
			$childPage->initFromDatabaseID ($row['pageID']);
			$childPages[] = $childPage;
		}
		return $childPages;
	}
}
?>
