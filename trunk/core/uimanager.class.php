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
/** \file uimanager.class.php
 * File that take care of the main UI layer, extensionhandling and HTML output
 *
 * $Id$
 * \author Nathan Samson
*/
include_once ('core/compatible.php');
define ('MORGOS_VERSION', '0.1');
define ('MORGOS_SVN_REVISION', '$Rev$');
define ('MORGOS_EXTENSION_ID', '{0000-0000-0000-0000}');
define ('MORGOS_DEFAULT_SKIN', 'MorgOS Default');

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

/** \fn getFrom ($from, $name, &$var, $default = NULL)
 * gets a value from an array (use for PHP/HTTP arrays like post, get and session)
 * \warning $var becomes overwritten completely if $name is an array
 * \bug code copying in the whole function
 * \bug if $from is an array and $name is an array it returns something wrong!!!!
 *
 * \param $from (string, array string)
 * \param $name (string, array string)
 * \param &$var (mixed, array mixed)
 * \param $default (mixed, array mixed)
 * \param $strErrors (mixed array)
 * \return (bool) true if found, false if not
*/
function getFrom ($from, $name, &$var, $default = NULL, $strErrors = array ()) {
	if (is_array ($from)) {
		foreach ($from as $f) {
			switch ($f) {
				case 'post':
					$f = $_POST;
					break;
				case 'get':
					$f = $_GET;
					break;
				case 'session':
					$f = $_SESSION;
					break;
				default:
					$f = array ();
			}
			if (array_key_exists ($name, $f)) {
				$use = $from;
				break;
			}
		}
		$use = array (); // not found 
	} else {
		switch ($from) {
			case 'post':
				$use = $_POST;
				break;
			case 'get':
				$use = $_GET;
				break;
			case 'session':
				$use = $_SESSION;
				break;
			default:
				$use = array ();
		}
	}

	if (is_array ($name)) {
		$var = array ();
		$i = 0;
		$errors = array ();
		foreach ($name as $n) {
			if (array_key_exists ($n, $use)) {
				$var[$n] = $use[$n];
			} else {
				$var = $default[$i];
				if (count ($strErrors) >= $i+1) {
					$errors[] = $strErrors[$i];
				}
			}
			$i++;
		}
		
		if (count ($errors) == 0) {
			return true;
		} else {
			return $errors;
		}
	} else {
		if (array_key_exists ($name, $use)) {
			$var = $use[$name];
			if (empty ($var)) {
				return false;
			} else {
				return true;
			}
		} else {
			$var = $default;
			return false;
		}
	}
}

/** \class UIManager
 * class that take care of the main UI layer, extensionhandling and HTML output.
 *
 * \author Nathan Samson
 * \version 0.1svn
 * \bug in PHP <= 4.3 if an error occurs in the constructor, errorHandler can not be handled correctly
 * \bug lowest tested version is 4.1.0
 * \bug if a skin is enabled in site.config.php, but not installed it leaves enabled in site.config.php. 
 		When you install it is loaded.
 * \todo 0.1 change the dir in __construct to install in place of DOT install
 * \todo 0.1 check all input wich is outputted and from user (htmlspecialchars)
 * \todo 0.1 check for UBB hacks (when UBB is implmented)
 * \todo 0.1 redo the admin page
 * \todo 0.? installer: check for an already existing installation (both site.config.php and database)
 * \todo 0.? remove $this->module or $this->loadedModule;
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
		if (versionCompare (PHP_VERSION, '5.0', '>=')) {
			set_error_handler (array ($this, "errorHandler"));
		} else {
			set_error_handler ('errorHandler');
		}
		if (! file_exists ('site.config.php')) {
			header ('Location: install.php');
		}
		if (is_readable ('site.config.php')) {
			if (file_exists ('.install')) {
				if (is_dir ('.install')) {
					trigger_error ('ERROR: ' . $this->i10nMan->translate ('Remove dir install.php and than continue'));
				}
			}
			$reqFiles = array ();
			$reqFiles[] = 'core/compatible.php';
			$reqFiles[] = 'core/config.class.php';
			$reqFiles[] = 'core/database.class.php';
			$reqFiles[] = 'core/language.class.php';
			$reqFiles[] = 'core/pages.class.php';
			$reqFiles[] = 'core/signals.class.php';
			$reqFiles[] = 'core/uimanager.class.php';
			$reqFiles[] = 'core/uimanager.functions.php';
			$reqFiles[] = 'core/uimanager.vars.php';
			$reqFiles[] = 'core/user.class.php';
			$reqFiles[] = 'admin.php';
			$reqFiles[] = 'index.php';
			$reqFiles[] = 'skins/default/tinymce';
			//testFiles ($reqFiles);
		 	include_once ('core/config.class.php');
			include_once ('core/language.class.php');
			include_once ('core/signals.class.php');
			$this->i10nMan = new languages ('languages/');
			$this->signalMan = new signalManager ($this->i10nMan);
			$this->signalMan->addSignal ('loadPage');
			$this->signalMan->addSignal ('login');
			$this->signalMan->addSignal ('logout');
			$this->signalMan->addSignal ('registeruser');
			$this->config = new config ($this->i10nMan);
			$this->config->addConfigItemsFromFile ('site.config.php');
			if (! defined ('TBL_PREFIX')) {
				define ('TBL_PREFIX', 'morgos_');
				define ('TBL_MODULES', TBL_PREFIX . 'modules');
				define ('TBL_PAGES', TBL_PREFIX . 'userpages');
			}
			include_once ('core/database.class.php');
			include_once ('core/user.class.php');
			include_once ('core/news.class.php');
			include_once ('core/pages.class.php');
			$DBManager = new genericDatabase ($this->i10nMan);
			$this->genDB = $DBManager->load ($this->config->getConfigItem ('/database/type', TYPE_STRING));
			$this->genDB->connect ($this->config->getConfigItem ('/database/host', TYPE_STRING), $this->config->getConfigItem ('/database/user', TYPE_STRING),
			$this->config->getConfigItem ('/database/password', TYPE_STRING));
			$this->genDB->select_db ($this->config->getConfigItem ('/database/name', TYPE_STRING));
			$this->pages = new pages ($this->genDB, $this->i10nMan);
			$this->news = new news ($this->genDB, $this->i10nMan);
			if (! $this->i10nMan->loadLanguage ('english')) {
				trigger_error ('ERROR: ' . $this->i10nMan->translate ('Couldn\'t init internationalization.'));
			}
			$this->user = NULL;
			
			$this->loadedExtensions = array ();
			$this->initAllExtensions ();
			$this->prependSidebar = array ();
			$this->appendSidebar = array ();
			$this->prependSidebar['ALL_MODULES'] = array ();
			$this->appendSidebar['ALL_MODULES'] = array ();
			
			$this->prependSubbar = array ();
			$this->appendSubbar = array ();
			$this->prependSubbar['ALL_MODULES'] = array ();
			$this->appendSubbar['ALL_MODULES'] = array ();
			foreach ($this->config->getConfigDir ('/extensions') as $extension => $load) {
				if ($load == true) {
					$result = $this->loadExtension (substr ($extension, strlen ('/extensions/')));
					if ($result == false) {
						$this->setRunning (true);
						trigger_error ('WARNING: ' . $this->i10nMan->translate ('Couldn\'t load extension.'));
						$this->setRunning (false);
					}
				}
			}
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
		if ($this->user == NULL) {
			$this->user = new user ($this->genDB, $this->i10nMan);
		}
		return $this->user;
	}
	
	/** \fn getPagesClass ()
	 * returns the pagesclass
	 *
	 * \return class
	*/
	/*public*/ function &getPagesClass () {
		return $this->pages;
	}
	
	/** \fn loadPage ($moduleName, $language) 
	 * Echo a page with moduule $moduleName. If the user needs to be logged in to view this page (and he isn't) 
	 * an error is triggered, if he needs to be admin and he isn't als an error is triggered
	 *
	 * \param $moduleName (string)
	 * \param $language (string)
	*/ 
	/*public*/ function loadPage ($moduleName, $language = NULL) {
		$this->signalMan->execSignal ('loadPage', $moduleName, $language);
		if ($this->user == NULL) {
			$this->user = new user ($this->genDB, $this->i10nMan);
		}
		if ($this->user->isLoggedIn ()) {
			$userInfo = $this->user->getUser ();		
			$this->config->addConfigItem ('/userinterface/language', $userInfo['language'], TYPE_STRING);
			$this->config->addConfigItem ('/userinterface/contentlanguage', $userInfo['contentlanguage'], TYPE_STRING);
			$this->config->addConfigItem ('/userinterface/skin', $userInfo['skin'], TYPE_STRING);
		} else {
			$this->config->addConfigItem ('/userinterface/language', 'english', TYPE_STRING);
			$this->config->addConfigItem ('/userinterface/contentlanguage', 'english', TYPE_STRING);
			$this->config->addConfigItem ('/userinterface/skin', MORGOS_DEFAULT_SKIN, TYPE_STRING);
		}
		$this->prependSidebar[$moduleName] = array ();
		$this->appendSidebar[$moduleName] = array ();
		
		$this->prependSubbar[$moduleName] = array ();
		$this->appendSubbar[$moduleName] = array ();
		$this->loadSkin ($this->config->getConfigItem ('/userinterface/skin', TYPE_STRING));
		$this->module = $moduleName;
		$this->loadedModule = $moduleName;
		if (! $language) {
			$language = $this->config->getConfigItem ('/userinterface/contentlanguage', TYPE_STRING);
		} else {
			$this->config->changeValueConfigItem ('/userinterface/contentlanguage', $language);
		}

		$SQL = "SELECT needauthorized, needauthorizedasadmin, extension FROM " . TBL_MODULES . " WHERE module='$moduleName'";
		$query = $this->genDB->query ($SQL);
		if ($this->genDB->num_rows ($query) == 0) {
			trigger_error ('ERROR: ' . $this->i10nMan->translate ('Page does not exists.'));
			return;
		}
		$module = $this->genDB->fetch_array ($query);
		if (strtolower ($module['needauthorized']) == "yes" && $this->user->isLoggedIn () == false) {
			trigger_error ('ERROR: '. $this->i10nMan->translate ('You need to be logged in to access this page.'));
		}
		
		if (strtolower ($module['needauthorizedasadmin']) == "yes" && $this->user->isAdmin () == false) {
			trigger_error ('ERROR: ' . $this->i10nMan->translate ('You need to be admin to access this page.'));
		}

		if (file_exists ($this->skinPath . $moduleName . '.html')) {
			$output = file_get_contents ($this->skinPath . $moduleName . '.html');
		} elseif ($module['extension'] != MORGOS_EXTENSION_ID) {
			// look into the extension themes, first the one that is choosen by user then the default
			if (array_key_exists ($module['extension'], $this->loadedExtensions)) {
				$extension = $this->extensions[$module['extension']];
				$files = scandir ($extension['extension_dir'] . '/skins');
				$skinFile = null;
				foreach ($files as $fileName) {
					$file = $extension['extension_dir'] . '/skins/' . $fileName;
					if (($fileName[0] != '.') and (is_dir ($fileName))) {
						if (file_exists ($file . '/skin.php')) {
							$skin = array ();
							include ($file . '/skin.php');
							if ($skin['general']['name'] == $this->config->getConfig ('/userinterface/skin')) {
								$skinFile = $file . $moduleName . '.html';
							} elseif ($skin['general']['name'] == MORGOS_DEFAULT_SKIN) {
								$skinFile = $file . $moduleName . '.html';
							}
						}
					}
				}
				if ($skinFile == null) {
					$output = file_get_contents ($this->skinPath .'usermodule.html');
				} else {
					$output = file_get_contents ($skinFile);
				}
			} else {
				trigger_error ('ERROR: ' . $this->i10nMan->translate ('Extension is not loaded.'));
				return;
			}
		} else {
			// it is a module living in the database
			$output = file_get_contents ($this->skinPath . 'usermodule.html');
		}
		$this->running = true;
		$output = $this->parse ($output);
		global $startTime;
		list ($usec, $sec) = explode(" ",microtime());
		$endTime = ((float) $usec + (float) $sec);
		$this->vars['VAR_ERRORS'] = NULL;
		$this->vars['VAR_WARNINGS'] = NULL;
		$this->vars['VAR_NOTICES'] = NULL;
		$this->vars['VAR_DEBUGGING'] = NULL;
		foreach ($this->notices as $val) {
			$debug = $this->config->getConfigItem ('/general/debug', TYPE_BOOL);
			if (($val["type"] == "INTERNAL_ERROR") or ($val['type'] == "ERROR")) {
				$this->vars['VAR_ERRORS'] .= $val['error'];
			} elseif ($val["type"] == "NOTICE") {
				$this->vars['VAR_NOTICES'] .= $val['error'];
			} elseif ($val["type"] == "WARNING") {
				$this->vars['VAR_WARNINGS'] .= $val['error'];
			} elseif (($val["type"] == "DEBUG") or ($val["type"] == "PHP")){
				if ($debug) {
					$this->vars['VAR_DEBUGGING'] .=  $val['error'] . '<br />';
				}
			} else {
				if ($debug) {
					$this->vars['VAR_DEBUGGING'] .= $this->parse (' DEBUG (' . $val['error'] . ')');
				}
			}
		}
		$output = str_replace ('&VAR_ERRORS;', $this->vars['VAR_ERRORS'], $output);
		$output = str_replace ('&VAR_NOTICES;', $this->vars['VAR_NOTICES'], $output);
		$output = str_replace ('&VAR_WARNINGS;', $this->vars['VAR_WARNINGS'], $output);
		$output = str_replace ('&VAR_DEBUGGING;', $this->vars['VAR_DEBUGGING'], $output);
		
		echo str_replace ('&TIME_RUNNED;', $endTime - $startTime, $output);
	}
	
	/** \fn saveAdmin ($array)
	 * It saves site.config.php with all values.
	 *
	 * \param $array (mixed array) the array where the changed configItems live in
	 * \param $configItems (string) all configItems
	*/
	/*public*/ function saveAdmin ($array) {
		for ($i = 1; $i < func_num_args (); $i++) {
			$arg = func_get_arg ($i);
			if (array_key_exists ($arg, $array)) {
				$this->config->changeValueConfigItem ($arg, $array[$arg]);
			} else {
				trigger_error ('ERROR: ' . $this->i10nMan->translate ('Configuration not saved, new value is empty'));
			}
		}
		//define ('NEWLINE', "\n"); // TODO make this work also for WIndows and Mac endlines
		$debug = $this->config->getConfigItem ('/general/debug', TYPE_BOOL);
		if ($debug == true) {
			$debug = 'true';
		} else {
			$debug = 'false';
		}
		
		// write the config file out
		$output = '<?php ' . NEWLINE;
		$output .= '	/* This files is genereted by MorgOS, only change manual if you know what you are doing. */' . NEWLINE;
		$output .= '	$config[\'/general/sitename\'] = \'' . $this->config->getConfigItem ('/general/sitename', TYPE_STRING) ."';" . NEWLINE;
		$output .= '	$config[\'/general/debug\'] = ' . $debug .';' . NEWLINE;
		$output .= '	$config[\'/database/type\'] = \'' . $this->config->getConfigItem ('/database/type', TYPE_STRING) .'\';' . NEWLINE;
		$output .= '	$config[\'/database/name\'] = \'' . $this->config->getConfigItem ('/database/name', TYPE_STRING) .'\';' . NEWLINE;
		$output .= '	$config[\'/database/host\'] = \'' . $this->config->getConfigItem ('/database/host', TYPE_STRING) .'\';' . NEWLINE;
		$output .= '	$config[\'/database/user\'] = \'' . $this->config->getConfigItem ('/database/user', TYPE_STRING) .'\';' . NEWLINE ;
		$output .= '	$config[\'/database/password\'] = \'' . $this->config->getConfigItem ('/database/password', TYPE_STRING) .'\';' . NEWLINE;
		foreach ($this->config->getConfigDir ('/extensions') as $extension => $load) {
			if ($load == true) {
				$load = 'true';
			} else {
				$load = 'false';
			}
			$output .= '	$config[\''.$extension .'\'] = ' . $load .';' . NEWLINE;
			
		}
		//$output .= '	$config[\'/extensions/WHATEVER\'] = false;' . NEWLINE;
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
	
	/** \fn getNavigator ()
	 * Returns the HTML code for the normal navigator
	 *
	 * \return (string)
	*/
	/*public*/ function getNavigator () {
		$HTML = ' NAVIGATION_OPEN ()';
		$HTML .= $this->getNavigatorItem ('');
		$HTML .= ' NAVIGATION_CLOSE ()';
		$HTML = $this->parse ($HTML);
		return $HTML;
	}
	
	/** \fn getNavigatorItem ($parent, $depth = 1)
	 * Returns the HTML code for the one item (and all his childs)
	 *
	 * \param $parent (string)
	 * \param $depth (int)
	 * \return (string)
	*/
	/*public*/ function getNavigatorItem ($parent, $depth = 1) {
		/*You can have max Parent-Child-Baby*/
		if ($depth > 3) {
			return NULL;
		}
		$language = $this->config->getConfigItem ('/userinterface/contentlanguage', TYPE_STRING);

		$SQL = "SELECT tm.module, tp.name, tm.place, tm.parent, tm.islink, tm.extension FROM ".TBL_MODULES . " AS tm , " .TBL_PAGES ." AS tp  WHERE  tp.module=tm.module AND tp.language='$language' AND parent='$parent'";
		if ($this->user->isLoggedIn ()) {
			if (! $this->user->isAdmin ()) {
				$SQL .= " AND needauthorizedasadmin='no'";
			}
		} else {
			$SQL .= " AND needauthorized='no'";
		}
		$query = $this->genDB->query ($SQL);
		$HTML = NULL;
		$navigation = array ();
		$name = array ();
		$place = array ();
		while ($item = $this->genDB->fetch_array ($query)) {
			if ($item['place'] != 0) {
				$childs = $this->getNavigatorItem ($item['module'], $depth + 1);
				$item['childs'] = $childs;
				$navigation[] = $item;
			}
		}
		foreach ($navigation as $key => $data) {
			$name[$key]  = $data['name'];
			$place[$key] = $data['place'];
		}
		array_multisort ($place, SORT_ASC, $name, SORT_ASC, $navigation);
		foreach ($navigation as $item) {
			if (($item['extension'] == '{0000-0000-0000-0000}') or (array_key_exists ($item['extension'], $this->loadedExtensions))) {
				if ($item['childs'] != NULL) {
					if ($item['islink'] == 'yes') {
						$HTML .= ' NAVIGATION_ITEM_WITH_CHILDS ('.$item['name'].', index.php?module='.$item['module'].',' . $item['childs'] . ')';
					} else {
						$HTML .= ' NAVIGATION_ITEM_WITH_CHILDS_NOLINK ('.$item['name'].',' . $item['childs'] . ')';
					}
				} else {
					$HTML .= ' NAVIGATION_ITEM_WITHOUT_CHILDS ('.$item['name'].', index.php?module='.$item['module'].')';
				}
			}
		}		
		return $this->parse ($HTML);
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

	/** \fn getAllSupportedSkins ()
	 * returns all skins
	 *
	 * \return (array (string))
	*/
	/*public*/ function getAllSupportedSkins () {
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
				}
			}
		}
		return $supSkins;
	}
	
	/** \fn prependToSidebar ($what, $module = 'ALL_MODULES')
	 * Prepend elements to the sidebar.
	 *
	 * \param $what (string) the content which is prepended
	 * \param $module (string) the module, if NULL in all modules $what is prepended
	*/
	/*public*/ function prependToSidebar ($what, $module = 'ALL_MODULES') {
		$this->prependSidebar[$module][] = $what;
	}

	/** \fn appendToSidebar ($what, $module = 'ALL_MODULES')
	 * Append elements to the sidebar.
	 *
	 * \param $what (string) the content which is appended
	 * \param $module (string) the module, if NULL in all modules $what is appended
	*/
	/*public*/ function appendToSidebar ($what, $module = 'ALL_MODULES') {
		$this->appendSidebar[$module][] = $what;
	}
	
	/** \fn prependToSubbar ($what, $module = 'ALL_MODULES')
	 * Prepend elements to the subbar.
	 *
	 * \param $what (string) the content which is prepended
	 * \param $module (string) the module, if NULL in all modules $what is prepended
	*/
	/*public*/ function prependToSubbar ($what, $module = 'ALL_MODULES') {
		$this->prependSidebar[$module][] = $what;
	}

	/** \fn appendToSubbar ($what, $module = 'ALL_MODULES')
	 * Append elements to the subbar.
	 *
	 * \param $what (string) the content which is appended
	 * \param $module (string) the module, if NULL in all modules $what is appended
	*/
	/*public*/ function appendToSubbar ($what, $module = 'ALL_MODULES') {
		$this->appendSubbar[$module][] = $what;
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
			trigger_error ('ERROR: ' . $this->i10nMan->translate ('Couldn\'t load skin, unsupported skin'));
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
			$string = str_replace ('&' . $varName . ';', $varValue, $string);
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
			if (count ($function) != 0) {
				$regExp = '/\s' . strtoupper ($funcKey) .' \(([\w-\W][^)]*)\)/';
			} else {
				$regExp = '/\s' . strtoupper ($funcKey) .' \(()\)/';
			}
			preg_match_all ($regExp, $string, $matches);
			foreach ($matches[0] as $key => $match) {
				$funcParams = explode (',', $matches[1][$key]);
				switch (strtoupper ($funcKey)) {
					case 'FILE':
						$replace = $this->skinPath . $matches[1][0];
						break;
					case 'TO_POSTNEW_COMMENT':
						$replace = 'index.php?module=formpostcomment&onItem=' . $funcParams[0] . '&onNews=' . $funcParams[1];
						break;
					default:
						$replace = $this->parse ($skin['functions'][$funcKey]);
				}
				foreach ($function as $number => $name) {
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
		$language = $this->config->getConfigItem ('/userinterface/contentlanguage', TYPE_STRING);
		$SQL = "SELECT tm.placeinadmin, tm.module, tp.name FROM ".TBL_MODULES . " AS tm , " .TBL_PAGES ." AS tp  WHERE  tp.module=tm.module AND tp.language='$language' AND needauthorizedasadmin='yes'";
		$query = $this->genDB->query ($SQL);
		$HTML = 'ADMIN_NAVIGATION_OPEN ()';
		while ($page = $this->genDB->fetch_array ($query)) {
			if ($page['placeinadmin'] != 0) {
				$pages[] = $page;
				
			}
		}
		foreach ($pages as $key => $data) {
			$name[$key]  = $data['name'];
			$place[$key] = $data['placeinadmin'];
		}
		array_multisort ($place, SORT_ASC, $name, SORT_ASC, $pages);
		foreach ($pages as $item) {
			$pos = strpos ($item['module'], '/');
			if ($pos !== false)  {
				$moduleName = substr ($item['module'], $pos + 1);
			} else {
				$moduleName = $item['module'];
			}
			$link = './admin.php?module=' . $moduleName;
			$HTML .= ' ADMIN_NAVIGATION_ITEM (' .$item['name']. ', ' .$link. ')';
		}
		$HTML .= ' ADMIN_NAVIGATION_CLOSE ()';		
		return $HTML;
	}
	
	/*private*/ function getModuleAdminHTMLItem ($parent) {
		$SQL = "SELECT * FROM " . TBL_MODULES . " WHERE parent='$parent'";
		$query = $this->genDB->query ($SQL);
		$pages .= ' &VAR_ADMIN_MODULES_OPEN;';
		while ($module = $this->genDB->fetch_array ($query)) {
			if ($module['needauthorized'] == 'yes') {
				$authorizedOnly = ' ADMIN_MODULES_FORM_NEEDAUTHORIZE (NEED_AUTHORIZE' . $module['module'] .')';
			} else {
				$authorizedOnly = ' ADMIN_MODULES_FORM_NONEEDAUTHORIZE (NEED_AUTHORIZE' . $module['module'] .')';
			}
			$authorizedOnly = $this->parse ($authorizedOnly);
			
			if ($module['needauthorizedasadmin'] == 'yes') {
				$adminOnly = ' ADMIN_MODULES_FORM_ADMIN_ONLY (ADMIN_ONLY' . $module['module'] .')';
			} else {
				$adminOnly = ' ADMIN_MODULES_FORM_NOT_ADMIN_ONLY (ADMIN_ONLY' . $module['module'] .')';
			}
			$adminOnly = $this->parse ($adminOnly);
			
			$languages = $this->pages->getAllAvailableLanguagesFromModule ($module['module']);
			$textLang = $this->parse (' ADMIN_MODULES_FORM_OPEN_AVAILABLE_LANGUAGES (LANGUAGE_'.$module['module'] .')');
			foreach ($languages as $language) {
				$textLang .= $this->parse (' ADMIN_MODULES_FORM_ITEM_AVAILABLE_LANGUAGES ('. $language .')');
			}
			$textLang .= $this->parse (' ADMIN_MODULES_FORM_CLOSE_AVAILABLE_LANGUAGES ()');
			$submitName = 'VIEW_PAGE' . $module['module'];
			$addPage = 'ADD_PAGE' . $module['module'];
			$deletePage = 'DELETE_PAGE' . $module['module'];
			$deleteModule = 'DELETE_MODULE' . $module['module'];
			$editPage = 'EDIT_PAGE' . $module['module'];
			$placeUp = 'PLACE_UP'. $module['module'];
			$placeDown = 'PLACE_DOWN'. $module['module'];
			if (strtolower ($module['listedinadmin']) == 'yes') {
				if ($module['place'] != 0) {
					$childs = $this->parse ($this->getModuleAdminHTMLItem ($module['module']));
					$parent = $this->parse (' SELECT (test)');
					foreach ($this->pages->getAllAvailableModules () as $name) {
						$parent .= $this->parse (' OPTION ('.$name['module'].')' . "\n");
					}
					$parent .= $this->parse (' CLOSESELECT ()');
					$pages .= ' ADMIN_MODULES_ITEM_INNAVIGATOR ('.$module['module'] . ', ' . $authorizedOnly .', ' . $adminOnly .', '. $textLang . ', '. $submitName .', ' . $addPage .', ' . $deletePage .', ' . $deleteModule .', '. $editPage .', ' . $childs . ','. $parent .')';
				} else {
					$pages .= ' ADMIN_MODULES_ITEM_NOTINNAVIGATOR ('.$module['module'] . ', ' . $authorizedOnly .', ' . $adminOnly .', '. $textLang . ', '. $submitName .', ' . $addPage .', ' . $deletePage .', ' . $deleteModule .', '. $editPage . ')';
				}
			};
		}
		$pages .= ' &VAR_ADMIN_MODULES_CLOSE;';
		return $pages;
	}
	
	/*private*/ function getModuleAdminHTML () {
		$HTML = $this->getModuleAdminHTMLItem ('');
		return $this->parse ($HTML);
	}
	
	/*private*/ function getUserAdminHTML () {
		$HTML = " FORM ( admin.php?module=saveusers, post)";
		$HTML .= " &VAR_USER_ADMIN_OPEN;";
		foreach ($this->user->getAllUsers () as $user) {
			if (strtolower ($user['isadmin']) == 'yes') {
				$isAdmin = " ADMIN_USER_ISADMIN ($user[username])";
			} else {
				$isAdmin = " ADMIN_USER_ISNOTADMIN ($user[username])";
			}
			$isAdmin = $this->parse ($isAdmin);
			$HTML .= " ADMIN_USER ($user[username], $user[email], $isAdmin)";
		}
		$HTML .= " &VAR_USER_ADMIN_CLOSE;";
		$HTML .= " INPUT (submit, &VAR_SUBMIT_USERS_NAME;, &TEXT_SUBMIT_USERS;)";
		$HTML .= " CLOSEFORM ()";
		return $this->parse ($HTML);
	}
	
	/*private*/ function getSidebarHTML () {
		$sidebar = NULL;
		include ($this->skinPath . 'skin.php');
		foreach ($this->prependSidebar['ALL_MODULES'] as $element) {
			$sidebar .= ' ' . $element . ' ';
		}
		foreach ($this->prependSidebar[$this->loadedModule] as $element) {
			$sidebar .= ' ' . $element . ' ';
		}
		$sidebar .= $skin['variable']['sidebar'];
		foreach ($this->appendSidebar['ALL_MODULES'] as $element) {
			$sidebar .= ' ' . $element . ' ';
		}
		foreach ($this->appendSidebar[$this->loadedModule] as $element) {
			$sidebar .= ' ' . $element . ' ';
		}
		return $this->parse ($sidebar);
	}
	
	/*private*/ function getSubbarHTML () {
		$subbar = NULL;
		include ($this->skinPath . 'skin.php');
		foreach ($this->prependSubbar['ALL_MODULES'] as $element) {
			$subbar .= ' ' . $element . ' ';
		}
		foreach ($this->prependSubbar[$this->loadedModule] as $element) {
			$subbar .= ' ' . $element . ' ';
		}
		$subbar .= $skin['variable']['subbar'];
		foreach ($this->appendSubbar['ALL_MODULES'] as $element) {
			$subbar .= ' ' . $element . ' ';
		}
		foreach ($this->appendSubbar[$this->loadedModule] as $element) {
			$subbar .= ' ' . $element . ' ';
		}
		return $this->parse ($subbar);
	}
	
	/*private*/ function getHTMLLatestNewsItems () {
		$HTML = '&LATEST_NEWS_ITEMS_OPEN;';
		include ($this->skinPath . 'skin.php');
		foreach ($this->news->getAllNewsItems () as $i) {
			$item = $skin['functions']['latest_news_items_item'];//' LATEST_NEWS_ITEMS_ITEM (' . $i['subject'] . ',' . $i['message'] .') ';
			$item = str_replace ('SUBJECT', $i['subject'], $item);
			$item = str_replace ('MESSAGE', nl2br ($i['message']), $item);
			$item = str_replace ('ONNEWS', '1', $item);
			$item = str_replace ('ONITEM', $i['id'], $item);
			$language = $this->config->getConfigItem ('/userinterface/contentlanguage', TYPE_STRING);
			$topic = $this->news->getTopic ($name, $language);
			$item = str_replace ('TOPICIMGSRC', $topic['image'], $item);
			$HTML .= $item;
		}
		$HTML .= '&LATEST_NEWS_ITEMS_CLOSE;';
		$HTML = $this->parse ($HTML);
		return $HTML;
	}
	
	/** \fn errorHandler ($errNo, $errStr, $errFile = NULL, $errLine = 0, $errContext = NULL)
	 * the error handler
	*/
	/*private*/ function errorHandler ($errNo, $errStr, $errFile = NULL, $errLine = 0, $errContext = NULL) {
		if ($errNo == E_STRICT) {
			return;
		} elseif ($errNo != E_USER_NOTICE) {
			$type = 'PHP';
			$error = $errStr;
		} else {
			$pos = strpos ($errStr, ": ");
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
				$error = $error . ': ' . $errFile . '@' . $errLine;
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
				$error = $error . ': ' . $errFile . '@' . $errLine;
				$die = true;
				break;
			case "UKNOWN";
				$die = false;
				trigger_error ('DEBUG: ' . $this->i10nMan->translate ('Type is not set'));
				break;
			default:
				$die = true;
				//trigger_error ('INTERNAL_ERROR: Error type is unrecognized.');
		}
		$this->appendNotice ($error, $type, $die);
		if (($this->running == false) and ($die == true)) {
			$output = file_get_contents ("skins/default/error.html");
			$errors = NULL;
			foreach ($this->notices as $notice) {
				$errors .= $notice['error'] . '<br />';
			}
			echo str_replace ("&VAR_ERROR;", $errors, $output);
			die ();
		}
	}

	/** \fn initAllExtensions ()
	 * Check all extensions and assign an ID to every extension
	*/
	/*private*/ function initAllExtensions () {
		$this->extensions = array ();
		$files = scandir ('extensions');
		foreach ($files as $file) {
			$extension = array ();
			$extensionDir = 'extensions/' . $file;
			if ($file[0] != '.') {
					if (is_dir ($extensionDir)) {
						if (file_exists ($extensionDir . '/extension.php')) {
						if (is_file ($extensionDir . '/extension.php')) {
							include $extensionDir . '/extension.php';
							$minVersion = $extension['general']['minversion'];
							$maxVersion = $extension['general']['maxversion'];
							$extensionID = $extension['general']['ID'];
							$isCompatibleFunction = $extension['iscompatible_function'];
							if ($isCompatibleFunction != false) {
								if (! $isCompatibleFunction ($this->genDB)) {
									$compatible = false;
								} else {
									$compatible = true;
								}
							} else {
								$compatible = true;
							}
							if ($compatible) {
								if ($extension['need_install'] == true) {
									$isInstalledFunction = $extension['is_installed_function'];
									$installable = $isInstalledFunction ($this->genDB) ? false : true;
									$extension['is_installed'] = $isInstalledFunction ($this->genDB);
								} else {
									$installable = false;
									//$extension['is_isntalled']
								}
							
								$extension['installable'] = $installable;
								if (versionCompare (MORGOS_VERSION, $minVersion, '<') || versionCompare (MORGOS_VERSION, $maxVersion, '>')) {
									$status = 'incompatible';
								} elseif ($this->extensionIsLoaded ($extensionID)) {
									$status = 'loaded';
								} elseif ($installable == true) {
									$status = 'not_installed';
								} else {
									$status = 'ok';
									if (array_key_exists ('required_file', $extension)) {
										foreach ($extension['required_file'] as $reqFile) {
											if (! file_exists ($extensionDir . '/' . $reqFile)) {
												$status = 'missing_file';
											}
										}
									}
								}
							} else {
								$status = 'incompatible';
							}
							$extension['status'] = $status;
							$extension['extension_dir'] = $extensionDir;
							$ID = $extension['general']['ID'];
							if (! array_key_exists ($ID, $this->extensions)) {
								$this->extensions[$ID] = $extension;
							} else {
								trigger_error ('ERROR: ' . $this->i10nMan->translate ('Extension hasn\'t an unique ID'));
							}
						}
					}
				}
			}
		}
	}

	/** \fn loadExtension ($extensionID)
	 * Loads an extension. Returns false on failure, true on success
	 *
	 * \param $extensionID(string)
	 * \return (bool)
	*/
	/*private*/ function loadExtension ($extensionID) {
		if (array_key_exists ($extensionID, $this->extensions)) {
			$extension = $this->extensions[$extensionID];
			if ($extension['status'] == 'ok') {
				if (array_key_exists ('file_to_load', $extension)) {
					$arrayOfObjects = array ();
					$arrayOfObjects['UI'] = $this;
					$loadedExtension = include ($extension['extension_dir'] . '/' . $extension['file_to_load']);
					$this->extensions[$extensionID]['status'] = 'loaded';
					$this->loadedExtensions[$extensionID] = $loadedExtension;
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/** \fn extensionIsLoaded ($extension)
	 * Is an extension loaded
	 *
	 * \param $extension (string)
	 * \return (bool)
	*/
	/*private*/ function extensionIsLoaded ($extension) {
		if (array_key_exists ($extension, $this->loadedExtensions)) {
			return true;
		} else {
			return false;
		}
	}
	
	/** \fn getExtensionAdminHTML ()
	 *
	*/
	/*private*/ function getExtensionAdminHTML () {
		$HTML = $this->parse (' &OPEN_EXTENSIONS_ADMIN;');
		foreach ($this->extensions as $extensionID => $extension) {
			$extensionName = $extension['general']['name'];
			$status = $extension['status'];
			$extensionLoad = 'load' . $extensionID;
			if ($status == 'loaded' || $status == 'ok') {
				if ($this->config->getConfigitem ('/extensions/' . $extensionID, TYPE_BOOL) == true) {
					$status = 'loaded';
				} else {
					$status = 'ok';
				}
			}
			switch ($status) {
				case 'loaded':
					$statusHTML = " ADMIN_EXTENSION_STATUS_LOADED ($extensionLoad)";
					break;
				case 'ok':
					$statusHTML = " ADMIN_EXTENSION_STATUS_OK ($extensionLoad)";
					break;
				case 'incompatible':
					$statusHTML = " ADMIN_EXTENSION_STATUS_INCOMPATIBLE ($extensionLoad)";
					break;
				case 'missing_file':
					$statusHTML = " ADMIN_EXTENSION_STATUS_MISSING_FILE ($extensionLoad)";
					break;
				case 'not_installed':
					$statusHTML = " ADMIN_EXTENSION_STATUS_NOT_INSTALLED ($extensionLoad)";
					break;
				default: 
					trigger_error ('NOTICE: ' . $this->i10nMan->translate ('Unrecognized extension-status.'));
					$statusHTML = NULL;
			}
			$installExtension = 'admin.php?module=installextension&name=' . $extensionID;
			$unInstallExtension = 'admin.php?module=uninstallextension&name=' . $extensionID;
			if ($status == 'not_installed') {
				$install = " ADMIN_EXTENSION_INSTALL ($installExtension)";
			} elseif ($extension['is_installed'] == true) {
				$install = " ADMIN_EXTENSION_UNINSTALL ($unInstallExtension)";
			} else {
				$install = NULL;
			}
			$statusHTML = $this->parse ($statusHTML);
			$install = $this->parse ($install);
			$HTML .= " ADMIN_EXTENSION_ITEM ($extensionName, $statusHTML, $install)";
		}
		$HTML .= ' &CLOSE_EXTENSIONS_ADMIN;';
		return $this->parse ($HTML);
	}
	
	function installExtension ($extensionID) {
		if (array_key_exists ($extensionID, $this->extensions)) {
			$extension = $this->extensions[$extensionID];
			if ($extension['installable'] == true) {
				$extension['install_function'] ($this->genDB, $this->pages);
			}
		} else {
			trigger_error ('ERROR: ' . $this->i10nMan->translate ('Extension does not exists.'));
		}
	}
	
	function unInstallExtension ($extensionID) {
		if (array_key_exists ($extensionID, $this->extensions)) {
			$extension = $this->extensions[$extensionID];
			if (($extension['installable'] == false) and ($extension['need_install'] == true)) {
				$extension['uninstall_function'] ($this->genDB, $this->pages);
			}
		} else {
			trigger_error ('ERROR: ' . $this->i10nMan->translate ('Extension does not exists.'));
		}
	}
}
?>
