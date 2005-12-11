<?php
/* MorgOS is a Content Management System written in PHP
 * Copyright (C) 2005 MorgOS
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
/** \file uimanager.class.php
 * File that take care of the main UI layer, extensionhandling and HTML output
 *
 * \author Nathan Samson
*/
error_reporting (E_ALL);
include_once ('core/compatible.php');
define ('MORGOS_VERSION', '0.1');

/** \fn errorHandler ($errNo, $errStr, $errFile = NULL, $errLine = 0, $errContext = NULL)
 * the error handler, this is a link to the one in UIManager (since it works otherwise not in PHP <= 4.3 )
*/
function errorHandler ($errNo, $errStr, $errFile = NULL, $errLine = 0, $errContext = NULL) {
	global $UI;
	if (empty ($UI)) {
		echo 'ERROR: ' . $errStr;
		die ();
	}
	$UI->errorHandler ($errNo, $errStr, $errFile, $errLine, $errContext);
}

/** \class UIManager
 * class that take care of the main UI layer, extensionhandling and HTML output.
 *
 * \author Nathan Samson
 * \version 0.1svn
 * \bug in PHP <= 4.3 if an error occurs in the constructor, errorHandler can not be handled correctly
 * \bug lowest tested version is 4.1.0
 * \bug If a module is deleted but not all pages are deleted this pages are not deleted
 * \bug Register is in the navigator
 * \todo change the dir in __construct to install in place of DOT install
 * \todo check all input wich is outputted and from user (htmlspecialchars)
 * \todo check for UBB hacks (when UBB is implmented)
*/
class UIManager {
	/*private $genDB;
	private $config
	private $user*/

	function UIManager () {
		$this->__construct ();
	}

	function __construct () {
		$this->notices = array ();
		$this->running = false;
		if (versionCompare (PHP_VERSION, '4.3', '>=')) {
			$errorHandler = array ($this, "errorHandler");
		} else {
			$errorHandler = 'errorHandler';
		}
		if (versionCompare (PHP_VERSION, '5.0', '>=')) {
			set_error_handler ($errorHandler, E_USER_NOTICE);
		} else {
			set_error_handler ($errorHandler);
		}
		if (! file_exists ('site.config.php')) {
			header ('Location: install.php');
		}
		if (is_readable ('site.config.php')) {
			if (file_exists ('.install')) {
				if (is_dir ('.install')) {
					trigger_error ('ERROR: Remove dir install.php and than continue');
				}
			}
		 	include_once ('core/config.class.php');
			$this->config = new config ();
			$this->config->addConfigItemsFromFile ('site.config.php');
			$this->config->addConfigItem ('/userinterface/language', 'english', TYPE_STRING);
			$this->config->addConfigItem ('/userinterface/contentlanguage', 'english', TYPE_STRING);
			define ('TBL_PREFIX', 'morgos_');
			define ('TBL_MODULES', TBL_PREFIX . 'modules');
			define ('TBL_INTERNAL_MODULES', TBL_PREFIX . 'internal_modules');
			define ('TBL_PAGES', TBL_PREFIX . 'userpages');
			include_once ('core/database.class.php');
			include_once ('core/user.class.php');
			include_once ('core/language.class.php');
			$DBManager = new genericDatabase ();
			$this->genDB = $DBManager->load ($this->config->getConfigItem ('/database/type', TYPE_STRING));
			$this->genDB->connect ($this->config->getConfigItem ('/database/host', TYPE_STRING), $this->config->getConfigItem ('/database/user', TYPE_STRING),
			$this->config->getConfigItem ('/database/password', TYPE_STRING));
			$this->genDB->select_db ($this->config->getConfigItem ('/database/name', TYPE_STRING));
			$this->user = new user ($this->genDB);	
			$this->i10nMan = new languages ();
			if (! $this->i10nMan->loadLanguage ('nederlands')) {
				trigger_error ('ERROR: Couldn\'t init internationalization.');
			}

			$this->loadSkin ('MorgOS Default');
		} else {
			header ('Location: install.php');
		}
	}
	
	/** \fn getGenericDB ()
	 * returns the generic DB class
	 *
	 * \return class
	*/
	/*public*/ function &getGenericDB () {
		return $this->genDB;
	}
	
	/** \fn getConfigClass ()
	 * returns the configclass
	 *
	 * \return class
	*/
	/*public*/ function &getConfigClass () {
		return $this->config;
	}
	
	/** \fn getUserClass ()
	 * returns the configclass
	 *
	 * \return class
	*/
	/*public*/ function &getUserClass () {
		return $this->user;
	}
	
	/** \fn loadPage ($moduleName, $language,$authorized = false,$authorizedAsAdmin = false) 
	 * Echo a page with moduule $moduleName. If the user needs to be logged in to view this page (and he isn't) 
	 * an error is triggered, if he needs to be admin and he isn't als an error is triggered
	 *
	 * \param $moduleName (string)
	 * \param $language (string)
	 * \param $authorized (bool) standard false
	 * \param $authorizedAsAsdmin (bool) standard false
	*/ 
	/*public*/ function loadPage ($moduleName, $language = NULL) {
		$this->module = $moduleName;
		if (! $language) {
			$language = $this->config->getConfigItem ('/userinterface/contentlanguage', TYPE_STRING);
		} else {
			$this->config->changeValueConfigItem ('/userinterface/contentlanguage', $language);
		}

		$SQL = "SELECT needauthorized, needauthorizedasadmin FROM " . TBL_MODULES . " WHERE module='$moduleName'";
		$query = $this->genDB->query ($SQL);
		$module = $this->genDB->fetch_array ($query);
		if ($module['needauthorized'] == strtolower ("YES") && $this->user->isLoggedIn () == false) {
			trigger_error ("ERROR: You need to be logged in to access this page.");
		}
		
		if ($module['needauthorizedasadmin'] == strtolower ("YES") && $this->user->isAdmin () == false) {
			trigger_error ("ERROR: You need to be admin to access this page.");
		}

		if (file_exists ($this->skinPath . $moduleName . '.html')) {
			$output = file_get_contents ($this->skinPath . $moduleName . '.html');
		} else {
			// it is a module living in the database
			$output = file_get_contents ($this->skinPath . 'usermodule.html');
		}
		$this->running = true;
		echo $this->parse ($output);
	}
	
	/** \fn saveAdmin ($array, $configItems)
	 * It saves site.config.php with all values.
	 *
	 * \param $array (mixed array) the array where the changed configItems live in
	 * \param $configItems (string) all configItems
	*/
	/*public*/ function saveAdmin ($array, $configItems) {
		for ($i = 1; $i < func_num_args (); $i++) {
			$arg = func_get_arg ($i);
			if (array_key_exists ($arg, $array)) {
				$this->config->changeValueConfigItem ($arg, $array[$arg]);
			} else {
				trigger_error ('ERROR: Configuration not saved, new value is empty');
			}
		}
		define ('NEWLINE', "\n"); // TODO make this work also for WIndows and Mac endlines
				
		// write the config file out
		$output = '<?php ' . NEWLINE;
		$output .= '	/* This files is genereted by MorgOS, only change manual if you know what you are doing. */' . NEWLINE;
		$output .= '	$config[\'/general/sitename\'] = \'' . $this->config->getConfigItem ('/general/sitename', TYPE_STRING) ."';" . NEWLINE;
		$output .= '	$config[\'/database/type\'] = \'' . $this->config->getConfigItem ('/database/type', TYPE_STRING) .'\';' . NEWLINE;
		$output .= '	$config[\'/database/name\'] = \'' . $this->config->getConfigItem ('/database/name', TYPE_STRING) .'\';' . NEWLINE;
		$output .= '	$config[\'/database/host\'] = \'' . $this->config->getConfigItem ('/database/host', TYPE_STRING) .'\';' . NEWLINE;
		$output .= '	$config[\'/database/user\'] = \'' . $this->config->getConfigItem ('/database/user', TYPE_STRING) .'\';' . NEWLINE ;
		$output .= '	$config[\'/database/password\'] = \'' . $this->config->getConfigItem ('/database/password', TYPE_STRING) .'\';' . NEWLINE;
		$output .= '?>';
		$fHandler = @fopen ('site.config.php', 'w');
		if ($fHandler !== false) {
			fwrite ($fHandler, $output);
			fclose ($fHandler);
			return true;
		} else {
			global $saveOutput;
			$saveOutput =  htmlentities ($output);
			$saveOutput = nl2br ($saveOutput);
			$this->loadPage ('admin/savemanually');
			return false;
		}
	}
	
	/** \fn getAllAvailableModules ()
	 * Returns an array of all available modules
	 *
	 * \return (string array)
	*/
	/*public*/ function getAllAvailableModules () {
		$SQL = 'SELECT * FROM ' . TBL_MODULES;
		$available = array ();
		$result = $this->genDB->query ($SQL);
		while ($row = $this->genDB->fetch_array ($result)) {
			$available[$row['module']] = $row;
		}
		return $available;
	}
	
	/** \fn getModuleContent ($module = NULL, $language = NULL)
	 * Returns the content of a module
	 *
	 * \param $module (string) the name of the module, is standard the loaded module
	 * \param $language (string) the language is standard the contentlanguage
	 * \return (string)
	*/
	/*public*/ function getModuleContent ($module = NULL, $language = NULL) {
		if (! $module) {
			$module = $this->module;
		}
		if (! $language) {
			$language = $this->config->getConfigItem ('/userinterface/contentlanguage', TYPE_STRING);
		}
		$SQL = 'SELECT content FROM ' . TBL_PAGES . ' WHERE module=\''. $module . '\'  AND language=\'' . $language . '\'';
		$result = $this->genDB->query ($SQL);
		while ($row = $this->genDB->fetch_array ($result)) {
			return $row['content'];
		}
	}
	
	/** \fn getNavigator ()
	 * Returns the HTML code for the normal navigator
	 *
	 * \warning On the moment all elements are showed there is no look to parent
	 * \return (string)
	*/
	/*public*/ function getNavigator () {
		$HTML = ' NAVIGATION_OPEN ()';
		$language = $this->config->getConfigItem ('/userinterface/contentlanguage', TYPE_STRING);
		$SQL = "SELECT tm.module, tp.name FROM ".TBL_MODULES . " AS tm , " .TBL_PAGES ." AS tp  WHERE  tp.module=tm.module AND tp.language='$language' AND needauthorized='no' AND needauthorizedasadmin='no'";
		$query = $this->genDB->query ($SQL);
		while ($row = $this->genDB->fetch_array ($query)) {
			$HTML .= ' NAVIGATION_ITEM ('.$row['name'].', index.php?module='.$row['module'].')';
		}
		$HTML .= ' NAVIGATION_CLOSE ()';
		$HTML = $this->parse ($HTML);
		return $HTML;
	}
	
	/** \fn changeSettingsModule ($module, $needAuthorize, $adminOnly)
	 * changes the settings for a module
	 *
	 * \param $module (string)
	 * \param $needAuthorize (bool) 
	 * \param $adminOnly (bool) 
	*/
	/*public*/ function changeSettingsModule ($module, $needAuthorize, $adminOnly) {
		if ($needAuthorize) {
			$needAuthorize = 'yes';
		} else {
			$needAuthorize = 'no';
		}
		
		if ($adminOnly) {
			$adminOnly = 'yes';
		} else {
			$adminOnly = 'no';
		}
		$SQL = "UPDATE " . TBL_MODULES . " SET needauthorized='$needAuthorize', needauthorizedasadmin='$adminOnly' WHERE module='$module'";
		$this->genDB->query ($SQL);
	}
	
	/** \fn addModule ($module, $needAuthorize, $needAuthorizeAsAdmin, $isInternal = false)
	 * adds a module,
	 *
	 * \param $module (string)
	 * \param $needAuthorize (bool)
	 * \param $needAuthorizeAsAdmin (bool)
	 * \param $isInternal (bool) if true (standard false) the user don't see this page
	 * \return (bool) 
	*/
	/*public*/ function addModule ($module, $needAuthorize, $needAuthorizeAsAdmin, $isInternal = false) {
		if (array_key_exists ($module, $this->getAllAvailableModules ())) {
			return false;
		} else {
			if ($needAuthorize) {
				$needAuthorize = 'yes';
			} else {
				$needAuthorize = 'no';
			}
			if ($needAuthorizeAsAdmin) {
				$needAuthorizeAsAdmin = 'yes';
			} else {
				$needAuthorizeAsAdmin = 'no';
			}
			if ($isInternal == true) {
				$SQL = "INSERT into " . TBL_INTERNAL_MODULES;
			} else {
				$SQL = "INSERT into " . TBL_MODULES;
			}
			$SQL .= " (module,needauthorized,needauthorizedasadmin) VALUES ('$module','$needAuthorize','$needAuthorizeAsAdmin')";
			$result = $this->genDB->query ($SQL);
			if ($result !== false) {
				return true;
			} else {
				return false;
			}
		}
	}
	
	/** \fn addPage ($module, $language, $name, $content)
	 * adds a page
	 *
	 * \param $module (string)
	 * \param $language (string)
	 * \param $name (string)
	 * \param $content (string)
	 * \return (bool) 
	*/
	/*public*/ function addPage ($module, $language, $name, $content) {
		if (array_key_exists ($language, $this->getAllAvailableLanguagesFromModule ($language))) {
			return false;
		} else {
			$SQL = "INSERT into " . TBL_PAGES . " (module,name,language,content) VALUES ('$module','$name','$language','$content')";
			$result = $this->genDB->query ($SQL);
			if ($result !== false) {
				return true;
			} else {
				trigger_error ('INTERNAL_ERROR: Can\'t add page');
				return false;
			}
		}
	}
	
	/** \fn deletePage ($module, $language)
	 * deletes a page
	*/
	/*pulbic*/ function deletePage ($module, $language) {
		$SQL = "DELETE FROM " . TBL_PAGES . " WHERE module='$module' AND language='$language'";
		$result = $this->genDB->query ($SQL);
		if ($result !== false) {
			return false;
		} else {
			return true;
		}
	}
	
	/** \fn deleteModule ($module)
	 * deletes a module
	*/
	/*pulbic*/ function deleteModule ($module) {
		$SQL = "DELETE FROM " . TBL_MODULES . " WHERE module='$module'"; 
		$result = $this->genDB->query ($SQL);
		if ($result !== false) {
			return true;
		} else {
			return false;
		}
	}
	
	/** \fn getPageInfo ($module = NULL, $language = NULL)
	 * get all the page info
	 *
	 * \param $module (string) the module standard is the current loaded module
	 * \param $language (string) the language standard is the current contentlanguagez
	 * \return (bool | string array)
	*/
	/*pulbic*/ function getPageInfo ($module = NULL, $language = NULL) {
		if (! $module) {
			$module = $this->module;
		}
		if (! $language) {
			$language = $this->config->getConfigByName ('/userinterface/contentlanguage', TYPE_STRING);
		}
		$SQL = "SELECT * FROM " . TBL_PAGES . " WHERE module='$module' AND language='$language'"; 
		$result = $this->genDB->query ($SQL);
		if ($result !== false) {
			return $this->genDB->fetch_array ($result);
		} else {
			return false;
		}
	}
	
	/** \fn editPage ($module, $language, $newName, $newContent)
	 * edit the page
	 *
	 * \param $module (string)
	 * \param $language (string)
	 * \param $newName (string)
	 * \param $newContent (string)
	 * \return (bool)
	*/
	/*pulbic*/ function editPage ($module, $language, $newName, $newContent) {
		$SQL = "UPDATE " . TBL_PAGES . " SET name='$newName', content='$newContent' WHERE module='$module' AND language='$language'"; 
		$result = $this->genDB->query ($SQL);
		if ($result !== false) {
			return true;
		} else {
			return false;
		}
	}
		
	/** \fn appendNotice ($notice, $type, $die)
	 * append an error/notice/warning in the output
	 * \warning the notice will not always be showed, it depends on the user configuration
	 *
	 * \param $notice (string) the notice
	 * \param $type (string) the type (INTERNAL_ERROR | DEBUG | NOTICE | ERROR)
	 * \param $die (bool) if this is true the output stops after this error
	*/
	/*public*/ function appendNotice ($error, $type, $die) {
		$this->notices[] = array ("error" => $error, "type" => $type, "die" => $die);
	}
	
	/** \fn setRunning ($running)
	 * set if is running manually
	 * \warning use this with care
	 *
	 * \param $running if UIManager is busy with creating output, which is important for errorhandling
	*/
	/*public*/ function setRunning ($running) {
		$this->running = $running;
	}

	/** \fn getUserNavigation ()
	 * return HTML for the usernavigation.
	 *
	 * \return (string)
	*/
	/*public*/ function getUserNavigation () {
		$HTML = ' USER_NAVIGATION_OPEN ()';
		$language = $this->config->getConfigItem ('/userinterface/contentlanguage', TYPE_STRING);
		$SQL = "SELECT tm.*, tp.name FROM ".TBL_MODULES . " AS tm , " .TBL_PAGES ." AS tp  WHERE  tp.module=tm.module AND tp.language='$language'";
		$query = $this->genDB->query ($SQL);
		while ($row = $this->genDB->fetch_array ($query)) {
			// do this not with SQL => get some unexpected results
			if ($row['needauthorized'] == 'yes') {
				$HTML .= ' USER_NAVIGATION_ITEM ('.$row['name'].', index.php?module='.$row['module'].')';
			} elseif (($row['needauthorizedasadmin'] == 'yes') and ($this->user->isAdmin ())) {
				$HTML .= ' USER_NAVIGATION_ITEM ('.$row['name'].', index.php?module='.$row['module'].')';
			}
		}
		$HTML .= ' USER_NAVIGATION_CLOSE ()';
		$HTML = $this->parse ($HTML);
		return $HTML;
	}
	
	/** \fn loadSkin ($skinName)
	 * Loads all skin options
	 *
	 * \param $skinName (string) the name of the skin to load
	*/
	/*private*/ function loadSkin ($skinName) {
		$supported = array ();
		$handler = opendir ('skins');
		// $files = scandir ('skins'); PHP5 only :( 
		// foreach ($files as $file) PHP5 only :(
		$supSkins = array ();
		$skinPaths = array ();
		while (false !== ($file = readdir ($handler))) {
			// do not start with a point (.)
			if ((preg_match ('/^\w.*/i', $file) == 1) and (is_dir ('skins/' . $file)) and (is_file ('skins/' . $file . '/skin.php'))) {
				unset ($skin);
				include ('skins/' . $file . '/skin.php');
				if (versionCompare ($skin['general']['minversion'],MORGOS_VERSION,'<=') and (versionCompare ($skin['general']['maxversion'],MORGOS_VERSION,'>='))) {
					$supSkins[] = $skin['general']['name'];
					$skinPaths[] = 'skins/' . $file . '/';
				}
			}
		}
		$key = array_search ($skinName,$supSkins);
		// needs to be 3 = (===) because the key can be 0 and than it is the same as false
		// to be compatible with PHP <= 4.2 we need to have === NULL
		if (($key === false) or ($key === NULL)) {
			trigger_error ('ERROR: Couldn\'t load skin, unsupported skin');
		} else {
			$this->skinPath = $skinPaths[$key];
		}
	}
	
	/** \fn parse ($fileName)
	 * Parses a file and replaces all, what needs to be replaced.
	 * \warning Do not callthis function before you called loadSkin
	 *
	 * \param $string (string) the string
	 * \return string
	*/
	/*private*/ function parse ($string) {
		if ($this->running == false) {
			return;
		}
		$this->replaceAllVars ($string);
		$this->replaceAllFunctions ($string);
		return $string;
	}
	
	/** \fn replaceAllVars (&$string) 
	 * Replaces all system and exstension vars
	 * \warning Do not callthis function before you called loadSkin
	 *
	 * \param $string (string) the input (and also the output)
	 * \bug If one var is part of another and the first part is defined before the longer one we have a big problem
	*/
	/*private*/ function replaceAllVars (&$string) {
		include_once ('core/uimanager.vars.php');
		foreach ($this->vars as $varName => $varValue) {
			$string = str_replace ($varName, $varValue, $string);
		}
	}
	
	/** \fn replaceAllFunctions (&$string) 
	 * Replaces all system functions
	 * \warning Do not callthis function before you called loadSkin
	 *
	 * \param $string (string) the input (and also the output)
	*/
	/*private*/ function replaceAllFunctions (&$string) {
		include ($this->skinPath . 'skin.php');
		include_once ('core/uimanager.functions.php');
		foreach ($this->functions as $funcKey => $function) {
			if (count ($function['params']) != 0) {
				$regExp = '/\s' . $function['name'] .' \(([\w-\W][^)]*)\)/';
			} else {
				$regExp = '/\s' . $function['name'] .' \(()\)/';
			}
			preg_match_all ($regExp, $string, $matches);
			foreach ($matches[0] as $key => $match) {
				$funcParams = explode (',', $matches[1][$key]);
				$replace = $this->parse ($skin['functions'][$funcKey]);
				foreach ($function['params'] as $number => $name) {
					if (array_key_exists ($number, $funcParams)) {
						$replace = str_replace ($name, trim ($funcParams[$number]), $replace);
					} else {
						$replace = str_replace ($name, '', $replace);
					}
				}
				$string = str_replace ($match, $replace, $string);
			}
		}
	}
	 
	/** \fn getAdminNavigation ()
	 * It returns the HTML code for the admin navigation
	 *
	 * \return (string)
	*/
	/*private*/ function getAdminNavigator () {
		$pages = array ();
		$pages[] = array ('name' => 'TEXT_ADMIN_INDEX', 'link' => 'VAR_ADMIN_LINK_INDEX');
		$pages[] = array ('name' => 'TEXT_ADMIN_GENERAL', 'link' => 'VAR_ADMIN_LINK_GENERAL');
		$pages[] = array ('name' => 'TEXT_ADMIN_DATABASE', 'link' => 'VAR_ADMIN_LINK_DATABASE');
		$pages[] = array ('name' => 'TEXT_ADMIN_PAGES', 'link' => 'VAR_ADMIN_LINK_PAGES');
		$HTML = 'ADMIN_NAVIGATION_OPEN ()';
		foreach ($pages as $page) {
			if ($page['link'] != 'TO IMPLEMENT') {
				$HTML .= ' ADMIN_NAVIGATION_ITEM (' .$page['name']. ', ' .$page['link']. ')';
			}
		}
		$HTML .= ' ADMIN_NAVIGATION_CLOSE ()';
		return $HTML;
	}
	
	/** \fn getAllAvailableLanguagesFromModule ($module)
	 * Returns the language of all pages with a specified module
	 *
	 * \return (string array)
	*/
	/*private*/ function getAllAvailableLanguagesFromModule ($module) {
		$available = array ();
		$SQL = "SELECT language FROM ". TBL_PAGES . " WHERE module='$module'";
		$result = $this->genDB->query ($SQL);
		while ($page = $this->genDB->fetch_array ($result)) {
			$available[] = $page['language'];
		}
		return $available;
	}
	
	/** \fn errorHandler ($errNo, $errStr, $errFile = NULL, $errLine = 0, $errContext = NULL)
	 * the error handler
	*/
	/*private*/ function errorHandler ($errNo, $errStr, $errFile = NULL, $errLine = 0, $errContext = NULL) {
		$pos = strpos ($errStr, ": ");
		if ($errNo != E_USER_NOTICE) {
			$type = 'PHP';
			$error = $errStr;
		} else {
			if ($pos != 0) {
				$type = substr ($errStr, 0, $pos);
				$error = substr ($errStr, $pos + 2);
			} else {
				$type = 'UKNOWN';
				$error = $errStr;
			}
		}	
		switch ($type) {
			case "INTERNAL_ERROR":
				$die = true;
				break;
			case "DEBUG":
				$die = false;
				break;
			case "NOTICE":
				$die = false;
				break;
			case "ERROR":
				$die = true;
				break;
			case "WARNING":
				$die = false;
				break;
			case "PHP":
				$die = true;
				break;
			case "UKNOWN";
				$die = false;
				trigger_error ('DEBUG: Type is not set');
				break;
			default:
				$die = true;
				//trigger_error ('INTERNAL_ERROR: Error type is unrecognized.');
		}
		if ($this->running == false) {
			$output = file_get_contents ("skins/default/error.html");
			echo str_replace ("VAR_ERROR", $error, $output);
			die ();
		} else {
			$this->appendNotice ($error, $type, $die);
		}
	}
}
?>
