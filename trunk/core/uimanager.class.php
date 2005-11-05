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
include_once ('core/database.class.php');
include_once ('core/user.class.php');
include_once ('core/config.class.php');
include_once ('core/language.class.php');
include_once ('core/compatible.php');
define ('MORGOS_VERSION','0.1');
/** \class UIManager
 * class that take care of the main UI layer, extensionhandling and HTML output.
 *
 * \author Nathan Samson
 * \version 0.1svn
 * \bug not compatible with PHP lower than 4.1  (use of version_compare)
 * \bug not compatible with PHP 4.0.4 and lower (use of array_search)
 * \bug not compatible with PHP 4.0.0 and lower (use of trigger_error)
 * \todo change the dir in __construct to install in place of DOT install
*/
class UIManager {
	/*private $DBManager;
	private $genDB;
	private $config
	private $user*/

	function UIManager () {
		$this->__construct ();
	}

	function __construct () {
		if (is_readable ('site.config.php')) {
			if (is_dir ('.install')) {
				trigger_error ('Remove dir install.php and than continue');
			}
			$this->config = new config ();
			$this->config->addConfigItemsFromFile ('site.config.php');
			$this->config->addConfigItem ('/userinterface/language', 'english', TYPE_STRING);
			$this->config->addConfigItem ('/userinterface/contentlanguage', 'english', TYPE_STRING);
			define ('TBL_PREFIX', 'morgos_');
			define ('TBL_MODULES', TBL_PREFIX . 'modules');
			define ('TBL_PAGES', TBL_PREFIX . 'userpages');
			
			$this->DBManager = new genericDatabase ();
			$this->genDB = $this->DBManager->load ($this->config->getConfigItem ('/database/type', TYPE_STRING));
			$this->genDB->connect ($this->config->getConfigItem ('/database/host', TYPE_STRING), $this->config->getConfigItem ('/database/user', TYPE_STRING),
				$this->config->getConfigItem ('/database/password', TYPE_STRING), $this->config->getConfigItem ('/database/name', TYPE_STRING));
			$this->user = new user ($this->genDB);	
			$this->i10nMan = new languages ();
			if (! $this->i10nMan->loadLanguage ('nederlands')) {
				trigger_error ('Couldn\'t init internationalization.');
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
	
	/** \fn loadPage ($moduleName, $language,$authorized = false,$authorizedAsAdmin = false) 
	 * Echo a page with moduule $moduleName. If the user needs to be logged in to view this page (and he isn't) 
	 * an error is triggered, if he needs to be admin and he isn't als an error is triggered
	 *
	 * \param $moduleName (string)
	 * \param $language (string)
	 * \param $authorized (bool) standard false
	 * \param $authorizedAsAsdmin (bool) standard false
	 * \todo trigger errors if user is not logged in, but userclass needs to be written first
	*/ 
	/*public*/ function loadPage ($moduleName, $language = NULL, $authorized = false, $authorizedAsAdmin = false) {
		// User code needs to be implemented first
		$this->module = $moduleName;
		if (! $language) {
			$language = $this->config->getConfigItem ('/userinterface/contentlanguage', TYPE_STRING);
		} else {
			$this->config->changeValueConfigItem ('/userinterface/contentlanguage', $language);
		}
		if (preg_match ('/.html$/', $moduleName)) {
			$output = file_get_contents ($this->skinPath . $moduleName);
		} else {
			// it is a module living in the database
			$output = file_get_contents ($this->skinPath . 'usermodule.html');
		}
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
				trigger_error ('Configuration not saved, new value is empty');
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
			echo '<h2>Save the folowing text in the file "site.config.php" in the directory where MorgOS is installed, then continue.</h2>';
			$output =  htmlentities ($output);
			$output = nl2br ($output);
			echo $output;
			echo '<h2>End of the content of site.config.php</h2>';
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
		$SQL = "SELECT tm.module, tp.name FROM ".TBL_MODULES . " AS tm , " .TBL_PAGES ." AS tp  WHERE  tp.module=tm.module AND tp.language='$language'";
		$query = $this->genDB->query ($SQL);
		while ($row = $this->genDB->fetch_array ($query)) {
			$HTML .= ' NAVIGATION_ITEM ('.$row['name'].', index.php?module='.$row['name'].')';
		}
		$HTML .= ' NAVIGATION_CLOSE ()';
		$HTML = $this->parse ($HTML);
		return $HTML;
	}
	
	/** \fn changeSettingsModule ($module, $needAuthorize)
	 * changes the settings for a module
	 *
	 * \param $module (string)
	 * \param $needAuthorize (bool) 
	*/
	/*public*/ function changeSettingsModule ($module, $needAuthorize) {
		if ($needAuthorize) {
			$needAuthorize = 'yes';
		} else {
			$needAuthorize = 'no';
		}
		$SQL = "UPDATE " . TBL_MODULES . " SET needauthorized='$needAuthorize' WHERE module='$module'";
		$this->genDB->query ($SQL);
	}
	
	/** \fn addModule ($module, $needAuthorize)
	 * adds a module,
	 *
	 * \param $module (string)
	 * \param $needAuthorize (bool)
	 * \return (bool) 
	*/
	/*public*/ function addModule ($module, $needAuthorize) {
		if (array_key_exists ($module, $this->getAllAvailableModules ())) {
			return false;
		} else {
			if ($needAuthorize) {
				$needAuthorize = 'yes';
			} else {
				$needAuthorize = 'no';
			}
			$SQL = "INSERT into " . TBL_MODULES . " (module,needauthorized) VALUES ('$module','$needAuthorize')";
			$result = $this->genDB->query ($SQL);
			if ($result !== false) {
				return true;
			} else {
				return false;
			}
		}
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
		while (false !== ($file = readdir ($handler))) {
			$supSkins = array ();
			$skinPaths = array ();
			// do not start with a point (.)
			if ((preg_match ('/^\w.*/i', $file) == 1) and (is_dir ('skins/' . $file)) and (is_file ('skins/' . $file . '/skin.ini'))) {
				unset ($skin);
				$skin = parse_ini_file ('skins/' . $file . '/skin.ini',true);
				if (version_compare ($skin['general']['minversion'],MORGOS_VERSION,'<=') and (version_compare ($skin['general']['maxversion'],MORGOS_VERSION,'>='))) {
					$supSkins[] = $skin['general']['name'];
					$skinPaths[] = 'skins/' . $file . '/';
				}
			}
		}
		$key = array_search ($skinName,$supSkins);
		// needs to be 3 = (===) because the key can be 0 and than it is the same as false
		// to be compatible with PHP <= 4.2 we need to have === NULL
		if (($key === false) or ($key === NULL)) {
			trigger_error ('Couldn\'t load skin, unsupported skin', E_USER_ERROR);
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
		include_once ('uimanager.vars.php');
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
		$skinIni = parse_ini_file ($this->skinPath . 'skin.ini', true);
		include_once ('uimanager.functions.php');
		foreach ($this->functions as $funcKey => $function) {
			if (count ($function['params']) != 0) {
				$regExp = '/\s' . $function['name'] .' \(([\w-\W][^)]*)\)/';
			} else {
				$regExp = '/\s' . $function['name'] .' \(()\)/';
			}
			preg_match_all ($regExp, $string, $matches);
			foreach ($matches[0] as $key => $match) {
				$funcParams = explode (',', $matches[1][$key]);
				$replace = $this->parse ($skinIni['functions'][$funcKey]);
				foreach ($function['params'] as $number => $name) {
					$replace = str_replace ($name, trim ($funcParams[$number]), $replace);
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
	
	function getAllAvailableLanguagesFromModule ($module) {
		$available = array ();
		$SQL = "SELECT language FROM ". TBL_PAGES . " WHERE module='$module'";
		$result = $this->genDB->query ($SQL);
		while ($page = $this->genDB->fetch_array ($result)) {
			$available[] = $page['language'];
		}
		return $available;
	}
}
?>
