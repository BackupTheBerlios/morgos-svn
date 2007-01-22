<?php
/* MorgOS is a Content Management System written in PHP
 * Copyright (C) 2005-2007 MorgOS
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
/** \file actionmanager.class.php
 * File that take care of the actions
 *
 * @ingroup interface
 * @todo test this file
 * @since 0.2
 * @author Nathan Samson
*/

/**
 * A class that is the base class for all inputs
 * @since 0.2
 * @ingroup interface
 * @author Nathan Samson
*/
class BaseInput {
	var $_name;
	
	/**
	 * Constructor
	 *
	 * @param $name (string)
	*/
	function BaseInput ($name) {
		$this->_name = $name;
	}
	
	function checkInput ($from) {
		if ($this->isGiven ($from)) {
			return true;
		} else {
			return new Error ('EMPTY_INPUT', $this->_name);
		}
	}
	
	function isGiven ($from)  {
		$fromArray = $this->getFromArray ($from);
		if (array_key_exists ($this->_name, $fromArray)) {
			if ($fromArray[$this->_name] == null) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}
	
	function getValue ($from) {
		if ($this->isGiven ($from)) {
			$fromArray = $this->getFromArray ($from);
			return $fromArray[$this->_name];
		} else {
			return $this->checkInput ($from);
		}
	}
	
	function getFromArray ($from) {
		if (is_array ($from)) return $from;
		switch ($from) {
			case 'POST':
				return $_POST;
				break;
			case 'GET':
				return $_GET;
				break;
		}
	}
	
	function getName () { return $this->_name;}
}

/**
 * A string input class
 * @ingroup interface
 * @since 0.2
*/
class StringInput extends BaseInput {
}

/**
 * An int input class
 * @ingroup interface
 * @since 0.2
*/
class IntInput extends BaseInput {
	function getValue ($from) {
		return (int) parent::getValue ($from);
	}
}

/**
 * An email input class
 * @ingroup interface
 * @since 0.2
 * @since 0.3 An email adress is valid if
 *	- it is something like user@hostName.domain
 *	- domain is 2 OR 3 chars
*/
class EmailInput extends StringInput {
	function checkInput ($from) {
		$r = parent::checkInput ($from);
		if (isError ($r)) return $r;
		$value = $this->getValue ($from);
		$AtChar = strpos ($value, '@');
		$host = substr ($value, $AtChar+1);
		$user = substr ($value, 0, $AtChar);
		$domain = substr ($host, strpos ($host, '.')+1);
		$hostName = substr ($host, 0, strpos ($host, '.'));
		if ( strlen ($user) > 0 &&
			strlen ($hostName) > 0 &&
			strlen ($domain) >=2 ) {
			
			return true;
		} else {
			return new Error ('INVALID_EMAIL');
		}
	}
}

/**
 * An enum input class
 * @ingroup interface
 * @since 0.2
*/
class EnumInput extends BaseInput {
	var $_poss;

	function EnumInput ($name, $possibilities) {
		parent::BaseInput ($name);
		$this->_poss = $possibilities;
	}
	
	function checkInput ($from) {
		if (! in_array ($this->getValue ($from), $this->_poss)) {
			return new Error ('INVALID_CHOICE', $this->getValue ($from), $this->_name);
		} else {
			return true;
		}
	}
}

class BoolInput extends EnumInput {
	
	function BoolInput ($name) {
		parent::EnumInput ($name, array (true, false));
	}
	
	function getValue ($from) {
		if ($this->isGiven ($from)) {
			$fromArray = $this->getFromArray ($from);
			if ($fromArray[$this->_name] == 'Y') {
				return true;
			} elseif ($fromArray[$this->_name] == 'N') {
				return false;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

}

/**
 * An ID input class, an ID is an int and always positive.
 * @ingroup interface
 * @since 0.2
*/
class IDInput extends IntInput {
	function checkInput ($from) {
		$r = parent::checkInput ($from);
		if (isError ($r)) return $r;
		if (is_int ($this->getValue ($from)) && $this->getValue ($from) > 0) {
			return true;
		} else {
			return new Error ('INVALID_ID');
		}
	}
}

/**
 * An locale input class.
 * @ingroup interface
 * @since 0.2
*/
class LocaleInput extends BaseInput {
}

/**
 * A multiple input class
 * @ingroup interface
 * @since 0.3
*/
class MultipleInput extends BaseInput {
	var $_prefix;
	var $_childs;

	function MultipleInput ($prefix, $childs) {
		$this->_prefix = $prefix;
		$this->_childs = array ();
		foreach ($childs as $name=>$class) {
			if (is_array ($class)) {
				/*$classN = $class[0];
				$childs[] = new $classN ($prefix.$name, );*/
				//die ('Not yet implemented'.__FILE__.'@'.__LINE__);
			} else {
				$this->_childs[$name] = new $class ($prefix.$name);
			}
		}
	}
	
	function checkInput ($from) {
		if ($this->isGiven ($from)) {
			foreach ($this->_childs as $child) {
				$i = $child->checkInput ($from);
				if ($i !== true) {
					return $i;
				}
			}
			return true;
		} else {
			return new Error ('EMPTY_INPUT', $this->_name);
		}
	}
	
	function isGiven ($from) {
		foreach ($this->_childs as $child) {
			$a = $child->isGiven ($from);
			if (! $a) {
				return false;
			}
		}
		return true;
	}
	
	function getChildValue ($from, $name) {
		$c = $this->_childs[$name];
		return $c->getValue ($from);
	}	
	
	function getValue ($from) {
		/*should be overriden*/
		return new Error ('MULTIPLE_INPUT_HANDLER_GET_VALUE_NYI');
	}

	function getName () {return $this->_prefix;}
}

/**
 * A password input class. This is used when the user inputs a new password 
 *	(that should be repeated)
 * @ingroup interface
 * @since 0.3
*/
class PasswordNewInput extends MultipleInput {
	function PasswordNewInput ($name) {
		parent::MultipleInput ($name, array ('1'=>'StringInput', '2'=>'StringInput'));
	}

	function checkInput ($from) {
		$r = parent::checkInput ($from);
		if (! isError ($r)) {
			if ($this->getChildValue ($from, '1') == $this->getChildValue ($from, '2')) {
				return null;
			} else {
				return new Error ('PASSWORDS_NOT_EQUAL');
			}
		} else {
			return $r;
		}
	}
	
	function getValue ($from) {
		if ($this->isGiven ($from)) {
			return $this->getChildValue ($from, '1');
		} else {
			return null;
		}
	}
}

/**
 * A class that represents an action
 *
 * @ingroup interface
 * @since 0.2
 * @author Nathan Samson
*/
class Action {
	/**
	 * The name of the event.
	 * @protected
	*/
	var $_name;
	/**
	 * The method (POST or GET) where it gets the values.
	 * @protected
	*/
	var $_method;
	/**
	 * Array of required options.
	 * @protected
	*/
	var $_requiredOptions;
	/**
	 * Not required options.
	 * @protected
	*/
	var $_notRequiredOptions;
	/**
	 * The PHP callback.
	 * @protected
	*/
	var $_executor;
	/**
	 * The associated page
	 * @protected
	*/
	var $_pageName;
	/**
	 * If the action should auto trigger viewPage or viewAnyAdminPage
	 * @protected
	*/
	var $_autoTrigger;

	/**
	 * Constructor.
	 *
	 * @param $name (string)
	 * @param $method (string) POST or GET
	 * @param $executor (PHP callback)
	 * @param $requiredOptions (baseInput array)
	 * @param $notRequiredOptions (baseInput array) default array ()
	 * @param $pageName (string) default null
	 * @param $autoTrigger (bool) default true (only important if pageName)
	 * @since 0.3 parameter $pageName and $autoTrigger
	*/
	function Action ($name, $method, $executor, $requiredOptions, 
			$notRequiredOptions = array (), $pageName = null, $autoTrigger = true) {
		$this->_name = $name;
		$this->_method = $method;
		$this->_requiredOptions = $requiredOptions;
		$this->_notRequiredOptions = $notRequiredOptions;
		$this->_executor = $executor;
		$this->_pageName = $pageName;
		$this->_autoTrigger = $autoTrigger;
	}
	
	/**
	 * Executes the action and returns it result.
	 *
	 * @param $params (mixed array) array with the parameters to use, if empty use default source. 
	 * 	(GET/POST)
	 * @public
	 * @return (mixed)
	*/
	function execute ($params = array ()) {
		$vals = $this->getParameters ($params);
		if (! isError ($vals)) {
			return call_user_func_array ($this->_executor, $vals);
		} else {
			return $vals;
		}
	}
	
	function getName () {return $this->_name;}
	function getParameters ($default) {
		if ($default == array ()) {
			if ($this->_method == 'GET') {
				$a = $_GET;
			} else {
				$a = $_POST;
			}
		} else {
			$a = $default;
		}
		
		$vals = array ();
		$errors = array ();
		foreach ($this->_requiredOptions as $option) {
			if (is_string ($option)) {
				morgosBacktrace ();
				die ("Old actionmanager API used");
			}
			$cI = $option->checkInput ($this->_method);
			if (! isError ($cI)) {
				$vals[$option->getName ()] = $option->getValue ($this->_method);
			} else {
				$errors[] = $cI;
			}
		}

		
		foreach ($this->_notRequiredOptions as $option) {
			$cI = $option->checkInput ($this->_method);
			if (! isError ($cI)) {
				$vals[$option->getName ()] = $option->getValue ($this->_method);
			} elseif ($cI->is ('EMPTY_INPUT')) {
				$vals[$option->getName ()] = null;
			} else {
				$errors[] = $cI;
			}
		}

		if ($errors != array ()) {
			return new Error ('ACTIONMANAGER_INVALID_INPUT', $errors);
		}
		return $vals;
	}
	
	function getCorrectParameters ($default) {
		if ($default == array ()) {
			if ($this->_method == 'GET') {
				$a = $_GET;
			} else {
				$a = $_POST;
			}
		} else {
			$a = $default;
		}
		
		$vals = array ();
		foreach ($this->_requiredOptions as $option) {
			if (is_string ($option)) {
				if (array_key_exists ($option, $a)) {
					$vals[$option] = $a[$option];
				}
			} else {
				$cI = $option->checkInput ($this->_method);
				if (! isError ($cI)) {
					$vals[$option->getName ()] = $option->getValue ($this->_method);
				}
			}
		}
		
		foreach ($this->_notRequiredOptions as $option) {
			if (is_string ($option)) {
				if (array_key_exists ($option, $a)) {
					$vals[$option] = $a[$option];
				} else {
					$vals[$option] = null;
				}
			} else {
				$cI = $option->checkInput ($this->_method);
				if (! isError ($cI)) {
					$vals[$option->getName ()] = $option->getValue ($this->_method);
				} elseif ($cI->is ('EMPTY_INPUT')) {
					$vals[$option->getName ()] = null;
				}
			}
		}
		return $vals;
	}
	
	function getPageName () {return $this->_pageName;}
	
	function autoTrigger () {return $this->getPageName () && $this->_autoTrigger;}
	//function setAutoTrigger ($bool) {$this->_autoTrigger = $bool;}
}

/**
 * A class that manages the actions
 *
 * @ingroup interface
 * @since 0.2
 * @author Nathan Samson
*/
class ActionManager {
	/**
	 * The list of all action The key is the name of the action.
	 * @private
	*/
	var $_actionsList;
	/**
	 * The last action name.
	 * @private
	*/
	var $_lastActionName;
	/**
	 * An array of the last action parameters
	 * @private
	*/
	var $_lastActionParameters;
	/**
	 * The previous action name.
	 * @private
	*/
	//var $_previousAction;
	/**
	 * An array of the previous action parameters
	 * @private
	*/
	//var $_previousActionParameters;

	/**
	 * Constructor
	*/
	function ActionManager () {
		$this->_actionsList = array ();
		$this->_lastActionName = '';
		$this->_lastActionParameters = array ();
		//var_dump ($_SERVER);
		/*$this->_previousActionName = $_COOKIE['lastActionName']; 
		
		$params = array ();
		foreach ($_COOKIE as $n=>$v) {
			$k = substr ($n, 0, strlen ('lastActionParameters_'));
			if ($k == 'lastActionParameters_') {
				$z = substr ($n, strlen ('lastActionParameters_')); 
				$params[$z] = $v;
			}
		}	
		if ($params == array ()) {
			$params['stubKey'] = 'stubValue';
		}
		$this->_previousActionParameters = $params;*/
				
	}
	
	/**
	 * Destructor
	*/
	function shutdown () {
		$this->saveLastAction ();
	}
	
	/**
	 * Executes an action.
	 * @param $actionName (string)
	 * @param $var (mixed array)
	 * @param $cache (bool) if false the action parameters aren't cached (default = true)
	 * @since 0.3 parameter $cache
	*/	
	function executeAction ($actionName, $var = array (), $cache = true) {
		if ($this->existsAction ($actionName)) {
			$actionArray = $this->getAction ($actionName);
			$action = $actionArray['action'];
			if (! isError ($action->getParameters ($var))) {
				if ($cache) {
					$this->_lastActionName = $actionName;
					$this->_lastActionParameters = $action->getParameters ($var);
				}
				return $action->execute ($var);
			} else {
				if ($cache) {
					$this->_lastActionParameters = $action->getCorrectParameters ($var);
				}
				return $action->getParameters ($var);
			}
		} else {
			return new Error ('ACTIONMANAGER_ACTION_NOT_FOUND', $actionName);
		}
	}
	
	/**
	 * Adds an action
	 * @param $action (object action)
	 * @param $permissions (string array) The permissions required to run the action
	 * @public
	*/
	function addAction ($action, $permissions = array ()) {
		$actionName = $action->getName ();
		if (! $this->existsAction ($actionName)) {
			$this->_actionsList[$actionName] = 
				array ('action'=>$action, 'permissions'=>$permissions);
		} else {
			return new Error ('ACTIONMANAGER_ACTION_EXISTS', $actionName);
		}
	}
	
	/**
	 * Return a list of all actions
	 *
	 * @public
	 * @return (object action array)
	*/
	function getAllActions () {
		return $this->_actionsList;
	}
	
	/**
	 * Returns an action
	 *
	 * $actionName (string)
	 * @public
	 * @return (object action)
	*/
	function getAction ($actionName) {
		if ($this->existsAction ($actionName)) {
			return $this->_actionsList[$actionName];
		} else {
			return new Error ('ACTION_MANAGER_ACTION_NOT_FOUND', $actionName);
		}
	}
	
	/**
	 * Checks that an action exists
	 *
	 * @param $actionName (string)
	 * @public
	 * @return (bool)
	*/
	function existsAction ($actionName) {
		return array_key_exists ($actionName, $this->_actionsList);
	}
	
	/**
	 * Returns the header string for the previous action.
	 * @public
	 * @return (string)
	*/
	function getPreviousActionHeaderString () {
		return 'Location: '.$this->getPreviousActionLinkString ();
	}
	
	/**
	 * Returns the permissions that are required to execute an action.
	 * @param $actionName (string)
	 * @public
	 * @return (string array) 
	*/
	function getActionRequiredPermissions ($actionName) {
		$a = $this->getAction ($actionName);
		if (isError ($a)) {
			return $a;
		}
		return $a['permissions'];
	}
	
	function getPreviousActionLinkString () {	
		/*$paramString = '';	
		foreach ($this->_previousActionParameters as $k=>$v) {
			$paramString .= '&'.$k.'='.$v;
		}*/
		
		//return 'index.php?action='.$this->_previousActionName.$paramString;
		//var_dump ($_SERVER);
		return $_SERVER['HTTP_REFERER'];
	}
	
	function saveLastAction () {
		// clean last action
		/*foreach ($_COOKIE as $n=>$v) {
			if (substr ($n, 0, strlen ('lastActionParameters_')) == 'lastActionParameters_') {
				setcookie ($n, '');
			}
		}	
	
		setcookie ('lastActionName', $this->_lastActionName);
		foreach ($this->_lastActionParameters as $key=>$value) {
			setcookie ('lastActionParameters_'.$key, $value);
		}*/
	}
}

?>
