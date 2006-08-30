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
		$genericName = new dbField ('genericName', 'varchar (255)');
		$genericContent = new dbField ('genericContent', 'text');
		$parentPageID = new dbField ('parentPageID', 'int(11)');
		$placeInMenu = new dbField ('placeInMenu', 'int(4)');
		$placeInMenu->canBeNull = true;
		
		parent::databaseObject ($db, $allOptions, array ('genericName'=>$genericName, 'genericContent'=>$genericContent, 'parentPageID'=>$parentPageID, 'placeInMenu'=>$placeInMenu), 'pages', 'pageID', $parent);
	}

	/**
	 * Initializes the page from a generic name
	 *
	 * @param $genericName (string) The generic name
	 * @public
	*/
	function initFromGenericName ($genericName) {
		$fullTableName = $this->getFullTableName ();
		$genericName = $this->db->escapeString ($genericName);
		$sql = "SELECT * FROM $fullTableName WHERE genericName='$genericName'";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			if ($this->db->numRows ($q) == 1) {
				$row = $this->db->fetchArray ($q);
				$this->initFromArray ($row);
				$this->setOption ('ID', $row[$this->getIDName ()]);
			} else {
				return "ERROR_PAGE_GENERICNAME_DOESNT_EXISTS $genericName";
			}
		} else {
			return $q;
		}
	}
	
	/**
	 * Returns the content
	 *
	 * @public
	 * @return (string)
	*/
	function getGenericContent () {return $this->getOption ('genericContent');}
	
	/**
	 * Returns the generic name (title) of the page
	 *
	 * @public
	 * @return (string)
	*/
	function getGenericName () {return $this->getOption ('genericName');}
	
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
	 * Returns the parentPage.
	 *
	 * @public
	 * @return (object)
	*/
	function getParentPage () {
		$parentPage = $parent->newPage ();
		$parentPage->initFromPageID ($this->getParentPageID ());
		return $parentPage;
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
		$sql = "SELECT translatedPageID FROM $fullTranslationTableName WHERE pageID='{$this->getID ()}' AND languageCode='$languageCode'";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			if ($this->db->numRows ($q) == 1) {
				$row = $this->db->fetchArray ($q);
				$tPage = $this->getCreator ()->newTranslatedPage ();
				$tPage->initFromDatabaseID ($row['translatedPageID']);
				return $tPage;
			} else {
				if (strlen ($languageCode) > 2) {
					$firstLang = substr ($languageCode, 0, 2);
					return $this->getTranslation ($firstLang);
				} else {
					return "ERROR_PAGE_TRANSLATION_NOT_FOUND";
				}
			} 
			return $tPage;
		} else {
			return $q;
		}
	}
	
	function getAllTranslations () {
		$fullTranslationTableName = $this->db->getPrefix ().'translatedPages';
		$sql = "SELECT languageCode FROM $fullTranslationTableName WHERE pageID='{$this->getID ()}' ORDER BY languageCode ASC";
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
	
	function addTranslation ($translation) {
		if (! $this->translationExists ($translation->getLanguageCode ())) {
			$a['pageID'] = $this->getID ();
			$translation->updateFromArray ($a);
			return $translation->addToDatabase ();
		} else {
			return "ERROR_PAGE_TRANSLATION_EXISTS {$translation->getLanguageCode ()}";
		}
	}
	
	function removeTranslation ($translation) {
		if ($this->translationExists ($translation->getLanguageCode ())) {
			return $translation->removeFromDatabase ();
		} else {
			return "ERROR_PAGE_TRANSLATION_DOESNT_EXISTS {$translation->getLanguageCode ()}";
		}
	}
	
	function translationExists ($languageCode) {
		if (in_array ($languageCode, $this->getAllTranslations ())) {
			return true;
		} else {
			return false;
		}
	}
}
?>