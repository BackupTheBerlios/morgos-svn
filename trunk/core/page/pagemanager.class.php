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
/** \file pagemanager.class.php
 * Manager of the pages.
 *
 * @since 0.2
 * @author Nathan Samson
*/

include_once ('core/page/page.class.php');
include_once ('core/page/translatedpage.class.php');

class pageManager {
	/**
	 * The database module.
	 * @private
	*/
	var $db;
	
	/**
	 * Cached array for options for a page
	 * @private
	*/
	var $allOptionsForPage;
	
	/**
	 * Cached array for options for a translated page
	 * @private
	*/
	var $allOptionsForTranslatedPage;

	/**
	 * Constructor
	 *
	 * @param $db (obejct dbModule)
	*/
	function pageManager ($db) {
		$this->db = $db;
		$this->allOptionsForPage = null;
		$this->allOptionsForTranslatedPage = null;
	}
	
	/**
	 * Add a page to the database. If needed it reoders the menu items.
	 *  When a page is inserted, all pages with the same, or a higher place in the menu are placed upwards.
	 *  If it has no placeInMenu value (if it is zero) it is placed after all other menu items
	 * 
	 * @param $page (object page)
	 * @public 
	*/
	function addPageToDatabase (&$page) {
		$pageName = $page->getGenericName ();
		$pageExists = $this->pageExists ($pageName);
		if ($pageExists) {
			return new Error ('PAGEMANAGER_PAGE_EXISTS', $pageName);
		}	
	
		$parentPageID = $page->getParentPageID ();
		if (! is_numeric ($parentPageID)) {
			return new Error ('DATABASEOBJECT_SQL_INJECTION_ATTACK_FAILED', __FILE__, __LINE__);
		}	
	
		if (($page->getPlaceInMenu () == 0) or ($page->getPlaceInMenu () == null)) {
			$pagesTableName = $this->db->getPrefix ().'pages';
			$sql = "SELECT MAX(placeInMenu) FROM $pagesTableName WHERE parentPageID='$parentPageID'";
			$q = $this->db->query ($sql);
			if (! isError ($q)) {
				$row = $this->db->fetchArray ($q);
				$a['placeInMenu'] = $row['MAX(placeInMenu)']+1;
				$page->updateFromArray ($a);
			} else {
				return $q;
			}
		} else {
			$place = $page->getPlaceInMenu ();
			if (! is_numeric ($place)) {
				return new Error ('DATABASEOBJECT_SQL_INJECTION_ATTACK_FAILED', __FILE__, __LINE__);
			}
			$pagesTableName = $this->db->getPrefix ().'pages';
			$sql = "UPDATE $pagesTableName SET placeInMenu=(placeInMenu)+1 WHERE placeInMenu>=$place AND parentPageID='$parentPageID'";
			$q = $this->db->query ($sql);
			if (isError ($q)) {
				return $q;
			}
		}
		
		return $page->addToDatabase ();
	}
	
	/**
	 * Deletes a page from the database.
	 *
	 * @param $page (object page)
	 * @public
	*/
	function removePageFromDatabase ($page) {
		$pageName = $page->getGenericName ();
		$pageExists = $this->pageExists ($pageName);
		if (! $pageExists) {
			return new Error ('PAGEMANAGER_PAGE_DOESNT_EXISTS', $pageName);
		}
		
		$placeInMenu = $page->getPlaceInMenu ();
		$pagesDatabaseName = $this->db->getPrefix ().'pages';
		$parentPageID = $page->getParentPageID ();
		if (! is_numeric ($placeInMenu)) {
			return new Error ('DATABASEOBJECT_SQL_INJECTION_ATTACK_FAILED', __FILE__, __LINE__);
		}
		if (! is_numeric ($parentPageID)) {
			return new Error ('DATABASEOBJECT_SQL_INJECTION_ATTACK_FAILED', __FILE__, __LINE__);
		}
		$sql = "UPDATE $pagesDatabaseName SET placeInMenu=(placeInMenu-1) WHERE placeInMenu>=$placeInMenu AND parentPageID='$parentPageID'";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			return $page->removeFromDatabase ();
		} else {
			return $q;
		}
	}
	
	/**
	 * Creates a new page object.
	 *
	 * @public
	 * @return (object page)
	*/
	function newPage () {
		return new page ($this->db, $this->getAllOptionsForPage (), $this);
	}
	
	/**
	 * Checks that a page exists.
	 *
	 * @param $pageName (string) The pagename
	 * @public
	 * @return (bool)
	*/
	function pageExists ($pageName) {
		$fullPagesTableName = $this->db->getPrefix ().'pages';
		$pageName = $this->db->escapeString ($pageName);
		$sql = "SELECT COUNT(pageID) FROM $fullPagesTableName WHERE genericName='$pageName'";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			$row = $this->db->fetchArray ($q);
			if ($row['COUNT(pageID)'] == 1) {
				return true;
			} else {
				return false;
			}
		} else {
			return $q;
		}
	}
	
	/**
	 * Returns an array with all menu items. The first item in the array is the first menu item (duh)
	 * 
	 * @param $rootPage (object page) The root page.
	 * @public
	 * @return (object page array)
	*/	
	function getMenu ($rootPage) {
		$tableName = $this->db->getPrefix ().'pages';
		$parentPageID = $rootPage->getID ();
		if (! is_numeric ($parentPageID)) {
			return new Error ('DATABASEOBJECT_SQL_INJECTION_ATTACK_FAILED', __FILE__, __LINE__);
		}
		$sql = "SELECT pageID FROM $tableName WHERE parentPageID='$parentPageID' ORDER BY placeInMenu ASC";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			$allPages = array ();
			while ($pageRow = $this->db->fetchArray ($q)) {
				$newPage = $this->newPage ();
				$newPage->initFromDatabaseID ($pageRow['pageID']);
				$allPages[] = $newPage;
			}
			return $allPages;
		} else {
			return $q;
		}
	}
	
	/**
	 * Adds an extra option to the database for the pages.
	 *
	 * @param $newOption (object dbField) the new option
	 * @warning old page objects don't profit of this. 
	 *  Wait for a restart of the system (reload of the page) to be sure its applied.
	 * @bug when after adding one, someone ask wich exists the new is not added in.
	 *    if that "asker" want to do something with it on an old page object it can cause weird errors.
	 * @public
	*/
	function addOptionToPage ($newOption) {
		$curOptions = $this->getAllOptionsForPage ();
		if (! isError ($curOptions)) {
			if (! array_key_exists ($newOption->name, $curOptions)) {
				$newOption->canBeNull = true;
				$r = $this->db->addNewField ($newOption, $this->db->prefix.'pages');
				if (! isError ($r)) {
					$this->allOptionsForPage[$newOption->name] = $newOption;
				} else {
					return $r;
				}
			} else {
				return new Error ('PAGEMANAGER_OPTION_FORPAGE_EXISTS', $newOption->name);
			}
		} else {
			return $curOptions;
		}
	}
	
	/**
	 * Removes an extra option to the database for the pages.
	 *
	 * @param $optionName (string) the name of the option
	 * @warning old page objects don't profit of this. 
	 *  Wait for a restart of the system (reload of the page) to be sure its applied.
	 * @public
	*/
	function removeOptionForPage ($optionName) {
		$curOptions = $this->getAllOptionsForPage ();
		if (! isError ($curOptions)) {
			if (array_key_exists ($optionName, $curOptions)) {
				$r = $this->db->removeField ($optionName, $this->db->getPrefix ().'pages');
				if (! isError ($r)) {
					unset ($this->allOptionsForPage[$optionName]);
				} else {
					return $r;
				}
			} else {
				return new Error ('PAGEMANAGER_OPTION_FORPAGE_DOESNT_EXISTS', $optionName);
			}
		} else {
			return $curOptions;
		}
	}
	
	/**
	 * Returns an associative array with values of object dbField
	 *
	 * @return (object dbField array)
	 * @public
	*/
	function getAllOptionsForPage () {
		if ($this->allOptionsForPage === null) {
			$allOptions = $this->db->getAlldbFields ($this->db->getPrefix ().'pages', array ('pageID', 'genericName', 'genericContent', 'parentPageID', 'placeInMenu', 'action', 'pluginID'));
			if (! isError ($allOptions)) {
				$this->allOptionsForPage = $allOptions;
			}
			return $allOptions;
		} else {
			return $this->allOptionsForPage;
		}
	}
	
	/**
	 * Adds an extra option to the database for the translated pages.
	 *
	 * @param $newOption (object dbField) the  new option
	 * @warning old translated page objects don't profit of this. 
	 *  Wait for a restart of the system (reload of the page) to be sure its applied.
	 * @bug when after adding one, someone ask wich exists the new is not added in.
	 *    if that "asker" want to do something with it on an old translatedpage object it can cause weird errors.
	 * @public
	*/
	function addOptionToTranslatedPage ($newOption) {
		$curOptions = $this->getAllOptionsForTranslatedPage ();
		if (! isError ($curOptions)) {
			if (! array_key_exists ($newOption->name, $curOptions)) {
				$newOption->canBeNull = true;
				$r = $this->db->addNewField ($newOption, $this->db->prefix.'translatedPages');
				if (! isError ($r)) {
					$this->allOptionsForTranslatedPage[$newOption->name] = $newOption;
				} else {
					return $r;
				}
			} else {
				return new Error ('PAGEMANAGER_OPTION_FORTRANSLATEDPAGE_EXISTS', $newOption->name);
			}
		} else {
			return $curOptions;
		}
	}
	
	/**
	 * Removes an extra option to the database for the translatdepages.
	 *
	 * @param $optionName (string) the name of the option
	 * @warning old translatedpage objects don't profit of this. 
	 *  Wait for a restart of the system (reload of the page) to be sure its applied.
	 * @public
	*/
	function removeOptionForTranslatedPage ($optionName) {
		$curOptions = $this->getAllOptionsForTranslatedPage ();
		if (! isError ($curOptions)) {
			if (array_key_exists ($optionName, $curOptions)) {
				$r = $this->db->removeField ($optionName, $this->db->getPrefix().'translatedPages');
				if (! isError ($r)) {
					unset ($this->allOptionsForTranslatedPage[$optionName]);					
				} else {
					return $r;
				}
			} else {
				return new Error ('PAGEMANAGER_OPTION_FORTRANSLATEDPAGE_DOESNT_EXISTS', $optionName);
			}
		} else {
			return $curOptions;
		}
	}	
	
	/**
	 * Returns an associative array with values of type object dbField
	 *
	 * @return (object dbField array)
	 * @public
	*/
	function getAllOptionsForTranslatedPage () {
		if ($this->allOptionsForTranslatedPage === null) {
			$fields = $this->db->getAllFields ($this->db->getPrefix ().'translatedPages');
			if (! isError ($fields)) {
				$allOptions = $this->db->getAlldbFields ($this->db->getPrefix ().'translatedPages', array ('translatedPageID', 'translatedName', 'translatedContent', 'pageID', 'languageCode'));
				if (! isError ($allOptions)) {
					$this->allOptionsForTranslatedPage = $allOptions;
				}
				return $allOptions;
			} else {
				return $fields;
			}
		} else {
			return $this->allOptionsForTranslatedPage;
		}
	}

	/**
	 * Returns a new object translatedPage.
	 *
	 * @public
	 * @returns (object translatedPage)
	*/
	function newTranslatedPage () {
		return new translatedPage ($this->db, $this->getAllOptionsForTranslatedPage (), $this);
	}
}
