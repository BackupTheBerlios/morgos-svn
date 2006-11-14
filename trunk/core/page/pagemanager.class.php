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
 * @ingroup page core
 * @since 0.2
 * @author Nathan Samson
*/

/**
 * Page group description
 *
 * @defgroup page Page
*/

include_once ('core/page/page.class.php');
include_once ('core/page/translatedpage.class.php');

/**
 * A class that manages all pages.
 *
 * @ingroup page
 * @since 0.2
 * @since 0.3 its derived from DBTableManager
 * @author Nathan Samson
*/
class PageManager extends DBTableManager {
	/**
	 * Constructor
	 *
	 * @param $db (obejct dbModule)
	*/
	function PageManager ($db) {
		parent::DBTableManager ($db, 'pages', 'Page', 'translatedPages', 'TranslatedPage');
	}
	
	/**
	 * Add a page to the database. If needed it reoders the menu items.
	 *  When a page is inserted, all pages with the same, or a higher place in the menu are placed upwards.
	 *  If it has no placeInMenu value (if it is null) it is placed after all other menu items. If 0 its not visible in the menu.
	 * 
	 * @param $page (object page)
	 * @public 
	*/
	function addPageToDatabase (&$page) {
		$pageName = $page->getName ();
		$pageExists = $this->pageExists ($pageName);
		if ($pageExists) {
			return new Error ('PAGEMANAGER_PAGE_EXISTS', $pageName);
		}	
	
		$parentPageID = $page->getParentPageID ();
		if (! is_numeric ($parentPageID)) {
			return new Error ('DATABASEOBJECT_SQL_INJECTION_ATTACK_FAILED', __FILE__, __LINE__);
		}	
	
		if ($page->getPlaceInMenu () === 0) {
			// do nothing, everything should be OK
		} elseif ($page->getPlaceInMenu () === -1) {
			$parentPage = $page->getParentPage ();
			$pInMen = $parentPage->getMaxPlaceInMenu ();
			if (! isError ($pInMen)) {
				$a = array ();
				$a['placeInMenu'] = $pInMen; 
				$page->updateFromArray ($a);
			} else {
				return $pInMen;
			}
		} else {
			$place = $page->getPlaceInMenu ();
			if (! is_numeric ($place)) {
				return new Error ('DATABASEOBJECT_SQL_INJECTION_ATTACK_FAILED', __FILE__, __LINE__);
			}
			$pagesTableName = $this->_db->getPrefix ().'pages';
			$sql = "UPDATE $pagesTableName SET placeInMenu=(placeInMenu)+1 WHERE placeInMenu>=$place AND parentPageID='$parentPageID'";
			$q = $this->_db->query ($sql);
			if (isError ($q)) {
				return $q;
			}
		}
		return $page->addToDatabase ();
	}
	
	/**
	 * Moves a page up in the menu (if possible)
	 *
	 * @param $pageID (int)
	 * @public
	*/	
	function movePageUp ($pageID) {
		$page = $this->newPage ();
		$r = $page->initFromDatabaseID ($pageID);
		if (! isError ($r)) {
			$pagesTableName = $this->_db->getPrefix ().'pages';
			$placeInMenu = $page->getPlaceInMenu ();
			$ppID = $page->getParentPageID ();
			$sql = "UPDATE $pagesTableName SET placeInMenu=(placeInMenu)+1 WHERE placeInMenu=($placeInMenu)-1 AND placeInMenu>0 AND parentPageID='$ppID'";
			$a = $this->_db->query ($sql);
			if (isError ($a)) {
				return $a;
			}
			
			$sql = 'UPDATE '.$pagesTableName.' SET placeInMenu=(placeInMenu)-1 WHERE pageID=\''.$page->getID ().'\' AND placeInMenu>1';
			$a = $this->_db->query ($sql);
			if (isError ($a)) {
				return $a;
			}
		}  else {
			return $r;
		}
	} 
	
	/**
	 * Moves a page down in the menu (if possible)
	 *
	 * @param $pageID (int)
	 * @public
	*/	
	function movePageDown ($pageID) {
		$page = $this->newPage ();
		$r = $page->initFromDatabaseID ($pageID);
		if (! isError ($r)) {
			$pagesTableName = $this->_db->getPrefix ().'pages';
			$placeInMenu = $page->getPlaceInMenu ();
			$ppID = $page->getParentPageID ();
			$sql = "UPDATE $pagesTableName SET placeInMenu=(placeInMenu)-1 WHERE placeInMenu=($placeInMenu)+1 AND parentPageID='$ppID'";
			$a = $this->_db->query ($sql);
			if (isError ($a)) {
				return $a;
			}
			
			// check their was a menu item down the one to be moved
			if ($this->_db->affectedRows ($a) !== 0) {
				$sql = 'UPDATE '.$pagesTableName.' SET placeInMenu=(placeInMenu)+1 WHERE pageID=\''.$page->getID ().'\'';
				$a = $this->_db->query ($sql);
				if (isError ($a)) {
					return $a;
				}
			}
		}  else {
			return $r;
		}
	} 
	
	/**
	 * Deletes a page from the database.
	 *
	 * @param $page (object page)
	 * @public
	*/
	function removePageFromDatabase ($page) {
		$pageName = $page->getName ();
		$pageExists = $this->pageExists ($pageName);
		if (! $pageExists) {
			return new Error ('PAGEMANAGER_PAGE_DOESNT_EXISTS', $pageName);
		}
		
		$placeInMenu = $page->getPlaceInMenu ();
		$pagesDatabaseName = $this->_db->getPrefix ().'pages';
		$parentPageID = $page->getParentPageID ();
		if (! is_numeric ($placeInMenu)) {
			return new Error ('DATABASEOBJECT_SQL_INJECTION_ATTACK_FAILED', __FILE__, __LINE__);
		}
		if (! is_numeric ($parentPageID)) {
			return new Error ('DATABASEOBJECT_SQL_INJECTION_ATTACK_FAILED', __FILE__, __LINE__);
		}
		$sql = "UPDATE $pagesDatabaseName SET placeInMenu=(placeInMenu-1) WHERE placeInMenu>=$placeInMenu AND parentPageID='$parentPageID'";
		$q = $this->_db->query ($sql);
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
		return $this->createObject ('pages');
	}
	
	/**
	 * Checks that a page exists.
	 *
	 * @param $pageName (string) The pagename
	 * @public
	 * @return (bool)
	*/
	function pageExists ($pageName) {
		$fullPagesTableName = $this->_db->getPrefix ().'pages';
		$pageName = $this->_db->escapeString ($pageName);
		$sql = "SELECT COUNT(pageID) FROM $fullPagesTableName WHERE name='$pageName'";
		$q = $this->_db->query ($sql);
		if (! isError ($q)) {
			$row = $this->_db->fetchArray ($q);
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
		$tableName = $this->_db->getPrefix ().'pages';
		$parentPageID = $rootPage->getID ();
		if (! is_numeric ($parentPageID)) {
			return new Error ('DATABASEOBJECT_SQL_INJECTION_ATTACK_FAILED', __FILE__, __LINE__);
		}
		$sql = "SELECT pageID FROM $tableName WHERE parentPageID='$parentPageID' AND placeInMenu>0 ORDER BY placeInMenu ASC";
		$q = $this->_db->query ($sql);
		if (! isError ($q)) {
			$allPages = array ();
			while ($pageRow = $this->_db->fetchArray ($q)) {
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
	 * Returns a new object translatedPage.
	 *
	 * @public
	 * @returns (object translatedPage)
	*/
	function newTranslatedPage () {
		return $this->createObject ('translatedPages');
	}
}
