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

define ('MORGOS_MENU_FIRST', 0);
define ('MORGOS_MENU_LAST', 254);
define ('MORGOS_MENU_INVISIBLE', 255);
define ('MORGOS_MENU_APPEND', -1);

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
		parent::DBTableManager 
			($db, 'pages', 'Page', 'translatedPages', 'TranslatedPage');
	}
	
	/**
	 * Add a page to the database. If needed it reoders the menu items.
	 * 
	 * @param $page (object page)
	 * @public 
	*/
	function addPageToDatabase (&$page) {
		$pageName = $page->getName ();
		$pageExists = $this->pageExists ($pageName);
		if ($pageExists) {
			return new Error ('PAGE_EXISTS_ALREADY', $pageName);
		}	
	
		$parentPageID = $page->getParentPageID ();
		if (! is_numeric ($parentPageID)) {
			return new Error ('DATABASEOBJECT_SQL_INJECTION_ATTACK_FAILED', 
				__FILE__, __LINE__);
		}	
	
		if ($page->getPlaceInMenu () === MORGOS_MENU_INVISIBLE
			or $page->getPlaceInMenu () === MORGOS_MENU_FIRST
			or $page->getPlaceInMenu () === MORGOS_MENU_LAST) {
			// do nothing, everything should be OK
		} elseif ($page->getPlaceInMenu () === MORGOS_MENU_APPEND) {
			if (! $page->isRootPage ()) {
				$parentPage = $page->getParentPage ();
				$pInMen = $parentPage->getMaxPlaceInMenu ();
				if (! isError ($pInMen)) {
					$a = array ();
					$a['place_in_menu'] = $pInMen; 
					$page->updateFromArray ($a);
				} else {
					return $pInMen;
				}
			}
		} else {
			$place = $page->getPlaceInMenu ();
			if (! is_numeric ($place)) {
				return new Error ('DATABASEOBJECT_SQL_INJECTION_ATTACK_FAILED', 
					__FILE__, __LINE__);
			}
			$pagesTableName = $this->_db->getPrefix ().'pages';
			$sql = "UPDATE $pagesTableName SET place_in_menu=(place_in_menu)+1 
				WHERE place_in_menu>=$place 
				AND place_in_menu!=".MORGOS_MENU_INVISIBLE 
				." AND place_in_menu!=".MORGOS_MENU_LAST 
				." AND parent_page_id='$parentPageID'";
			$q = $this->_db->query ($sql);
			if (isError ($q)) {
				return $q;
			}
		}
		$r= $page->addToDatabase ();
		if (isError ($r)) {
			return $r;
		}
	}
	
	/**
	 * Moves a page up in the menu (if possible)
	 *
	 * @param $page (object Page)
	 * @public
	*/	
	function movePageUp ($page) {
		$pagesTableName = $this->_db->getPrefix ().'pages';
		$placeInMenu = $page->getPlaceInMenu ();
		if ($placeInMenu == 1) {
			return;
		}
		$ppID = $page->getParentPageID ();
		$sql = "UPDATE $pagesTableName SET place_in_menu=(place_in_menu)+1 
			WHERE place_in_menu=($placeInMenu)-1 AND place_in_menu!=".MORGOS_MENU_FIRST 
			." AND place_in_menu!=".MORGOS_MENU_LAST 
			." AND place_in_menu!=".MORGOS_MENU_INVISIBLE." AND parent_page_id='$ppID'";
		$a = $this->_db->query ($sql);
		if (isError ($a)) {
			return $a;
		}
		
		$sql = 'UPDATE '.$pagesTableName.' SET place_in_menu=(place_in_menu)-1 
			WHERE page_id=\''.$page->getID ().'\' AND place_in_menu!='.MORGOS_MENU_FIRST
			." AND place_in_menu!=".MORGOS_MENU_LAST
			." AND place_in_menu!=".MORGOS_MENU_INVISIBLE;
		$a = $this->_db->query ($sql);
		if (isError ($a)) {
			return $a;
		}
	} 
	
	/**
	 * Moves a page down in the menu (if possible)
	 *
	 * @param $page (object Page)
	 * @public
	*/	
	function movePageDown ($page) {
		$pagesTableName = $this->_db->getPrefix ().'pages';
		$placeInMenu = $page->getPlaceInMenu ();
		$parentPage = $page->getParentPage ();
		if ($placeInMenu == $parentPage->getMaxPlaceInMenu ()-1) {
			return;
		}
		$ppID = $page->getParentPageID ();
		$sql = "UPDATE $pagesTableName SET place_in_menu=(place_in_menu)-1 
			WHERE place_in_menu=($placeInMenu)+1 AND place_in_menu!=".MORGOS_MENU_FIRST 
			." AND place_in_menu!=".MORGOS_MENU_LAST 
			." AND place_in_menu!=".MORGOS_MENU_INVISIBLE." AND parent_page_id='$ppID'";
		$a = $this->_db->query ($sql);
		if (isError ($a)) {
			return $a;
		}
		
		$sql = 'UPDATE '.$pagesTableName.' SET place_in_menu=(place_in_menu)+1 
			WHERE page_id=\''.$page->getID ().'\' AND place_in_menu!='.MORGOS_MENU_FIRST
			." AND place_in_menu!=".MORGOS_MENU_LAST
			." AND place_in_menu!=".MORGOS_MENU_INVISIBLE;
		$a = $this->_db->query ($sql);
		if (isError ($a)) {
			return $a;
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
			return new Error ('PAGE_NOT_FOUND', $pageName);
		}
		
		$placeInMenu = $page->getPlaceInMenu ();
		$pagesDatabaseName = $this->_db->getPrefix ().'pages';
		$parentPageID = $page->getParentPageID ();
		if (! is_numeric ($placeInMenu)) {
			return new Error ('DATABASEOBJECT_SQL_INJECTION_ATTACK_FAILED', 
				__FILE__, __LINE__);
		}
		if (! is_numeric ($parentPageID)) {
			return new Error ('DATABASEOBJECT_SQL_INJECTION_ATTACK_FAILED', 
				__FILE__, __LINE__);
		}
		$sql = "UPDATE $pagesDatabaseName SET place_in_menu=(place_in_menu-1) 
			WHERE place_in_menu>=$placeInMenu 
			AND place_in_menu<".MORGOS_MENU_LAST." AND parent_page_id='$parentPageID'";
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
		$sql = "SELECT COUNT(page_id) as pages FROM $fullPagesTableName 
			WHERE name='$pageName'";
		$q = $this->_db->query ($sql);
		if (! isError ($q)) {
			$row = $this->_db->fetchArray ($q);
			if ($row['pages'] == 1) {
				return true;
			} else {
				return false;
			}
		} else {
			return $q;
		}
	}
	
	/**
	 * Returns an array with all menu items. 
	 * The first item in the array is the first menu item (duh)
	 * 
	 * @param $rootPage (object page) The root page.
	 * @public
	 * @return (object page array)
	*/	
	function getMenu ($rootPage) {
		$tableName = $this->_db->getPrefix ().'pages';
		$parentPageID = $rootPage->getID ();
		if (! is_numeric ($parentPageID)) {
			return new Error ('DATABASEOBJECT_SQL_INJECTION_ATTACK_FAILED', 
			__FILE__, __LINE__);
		}
		$sql = "SELECT page_id FROM $tableName WHERE parent_page_id='$parentPageID' 
			AND place_in_menu!=".MORGOS_MENU_INVISIBLE." ORDER BY place_in_menu ASC";
		$q = $this->_db->query ($sql);
		if (! isError ($q)) {
			$allPages = array ();
			while ($pageRow = $this->_db->fetchArray ($q)) {
				$newPage = $this->newPage ();
				$newPage->initFromDatabaseID ($pageRow['page_id']);
				$allPages[] = $newPage;
			}
			return $allPages;
		} else {
			return $q;
		}
	}
	
	function getAdminPage () {
		$admin = $this->newPage ();
		$admin->initFromName ('admin');
		return $admin;
	}
	
	function getSitePage () {
		$site = $this->newPage ();
		$site->initFromName ('site');
		return $site;
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
	
	function isInstalled () {
		if (parent::isInstalled ()) {
			$site = $this->getSitePage ();
			$admin = $this->getAdminPage ();
			return ($site->isInDatabase ()
				and $admin->isInDatabase ());
		} else {
			return false;
		}
	}	
	
	function installAllTables () {
		parent::installAllTables ();
		$site = $this->newPage ();
		$site->initFromArray (array (
			'name'=>'site',
			'parent_page_id'=>0
			));
		$this->addPageToDatabase ($site);
		
		$admin = $this->newPage ();
		$admin->initFromArray (array (
			'name'=>'admin',
			'parent_page_id'=>0
			));
		$this->addPageToDatabase ($admin);
	}
}
