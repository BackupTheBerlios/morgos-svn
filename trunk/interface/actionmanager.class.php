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
class baseInput {
	var $_name;
	
	/**
	 * Constructor
	 *
	 * @param $name (string)
	*/
	function baseInput ($name) {
		$this->_name = $name;
	}
	
	function checkInput ($from) {
		if ($this->isGiven ($from)) {
			return null;
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
			return null;
		}
	}
	
	function getFromArray ($from) {
		switch ($from) {
			case 'POST':
				return $_POST;
				break;
			case 'GET':
				return $_GET;
				break;
		}
	}
	
	function getName () { return $this->_name; }
}

/**
 * A string input class
 * @ingroup interface
 * @since 0.2
*/
class StringInput extends baseInput {
}

/**
 * An int input class
 * @ingroup interface
 * @since 0.2
*/
class IntInput extends baseInput {
}

/**
 * An email input class
 * @ingroup interface
 * @since 0.2
*/
class EmailInput extends StringInput {
}

/**
 * An enum input class
 * @ingroup interface
 * @since 0.2
*/
class EnumInput extends baseInput {
	var $_poss;

	function EnumInput ($name, $possibilities) {
		parent::baseInput ($name);
		$this->_poss = $possibilities;
	}
	
	function checkInput ($from) {
		if (! in_array ($this->getValue ($from), $this->_poss)) {
			return new Error ('INVALID_CHOICE', $this->getValue ($from), $this->_name);
		} else {
			return null;
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
				return null;
			}
		} else {
			return null;
		}
	}

}

/**
 * An ID input class, an ID is an int and always positive.
 * @ingroup interface
 * @since 0.2
*/
class IDInput extends baseInput {
	function checkInput ($from) {
		if ($this->isGiven ($from)) {
			if (is_int ($this->getValue ($from))) {
				return $this->getValue ($from) >= 0;
			} else {
				return false;
			}
		}
	}
	
/*	function getValue ($from) {
		if ($this->isGiven ($from)) {
			$fromArray = $this->getFromArray ($from);
			return (int) $fromArray[$this->_name];
		} else {
			return null;
		}
	}*/
}

/**
 * An locale input class.
 * @ingroup interface
 * @since 0.2
*/
class LocaleInput extends baseInput {
}

/**
 * A multiple input class
 * @ingroup interface
 * @since 0.3
*/
class MultipleInput extends baseInput {
	var $_prefix;
	var $_childs;

	function MultipleInput ($prefix, $childs) {
		$this->_prefix = $prefix;
		$this->_childs = array ();
		foreach ($childs as $name=>$class) {
			if (is_array ($class)) {
				/*$classN = $class[0];
				$childs[] = new $classN ($prefix.$name, );*/
				die ('Not yet implemented'.__FILE__.'@'.__LINE__);
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
		return true;
	}

	function getName () {return $this->_prefix;}
}

/**
 * A password input class. This is used when the user inputs a new password 
 *	(that should be repeated)
 * @ingroup interface
 * @since 0.2
*/
class PasswordNewInput extends baseInput {

	function checkInput ($from) {
		if ($this->isGiven ($from)) {
			$vals = $this->getValues ($from);
			if ($vals[0] == $vals[1]) {
				return null;
			} else {
				return new Error ('PASSWORDS_NOT_EQUAL');
			}
		} else {
			return new Error ('EMPTY_INPUT', $this->_name);
		}
	}
	
	function isGiven ($from)  {
		$fromArray = $this->getFromArray ($from);
		$a = (array_key_exists ($this->_name.'1', $fromArray) and 
			array_key_exists ($this->_name.'2', $fromArray));
		if ($a) {
			return ($fromArray[$this->_name.'1'] != null) 
				and ($fromArray[$this->_name.'2'] != null);
		} else {
			return false;
		}
	}
	
	function getValue ($from) {
		if ($this->isGiven ($from)) {
			$fromArray = $this->getFromArray ($from);
			return $fromArray[$this->_name.'1'];
		} else {
			return null;
		}
	}
	
	function getValue2 ($from) {
		if ($this->isGiven ($from)) {
			$fromArray = $this->getFromArray ($from);
			return $fromArray[$this->_name.'2'];
		} else {
			return null;
		}
	}

	function getValues ($from) {
		$array = $this->getFromArray ($from);
		$values = array ($this->getValue ($from), $this->getValue2 ($from));
		return $values;
	}
}

/**
 * A class that represents an action
 *
 * @ingroup interface
 * @since 0.2
 * @author Nathan Samson
*/
class action {
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
	 * Constructor.
	 *
	 * @param $name (string)
	 * @param $method (string) POST or GET
	 * @param $executor (PHP callback)
	 * @param $requiredOptions (baseInput array)
	 * @param $notRequiredOptions (baseInput array) default array ()
	 * @param $pageName (string) default null
	 * @since 0.3 parameter $pageName
	*/
	function action ($name, $method, $executor, $requiredOptions, 
			$notRequiredOptions = array (), $pageName = null) {
		$this->_name = $name;
		$this->_method = $method;
		$this->_requiredOptions = $requiredOptions;
		$this->_notRequiredOptions = $notRequiredOptions;
		$this->_executor = $executor;
		$this->_pageName = $pageName;
	}
	
	/**
	 * Executes the action and returns it result.
	 *
	 * @param $params (mixed array) array with the parameters to use, 
	 *	if empty use default source. (GET/POST)
	 * @public
	 * @return (mixed)
	*/
	function execute ($params) {
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
				if (array_key_exists ($option, $a)) {
					$vals[$option] = $a[$option];
				} else {
					$errors[] = 
						new Error ('ACTIONMANAGER_REQUIRED_OPTION_NOT_FOUND', $option);
				}
			} else {
				$cI = $option->checkInput ($this->_method);
				if (! isError ($cI)) {
					$vals[$option->getName ()] = $option->getValue ($this->_method);
				} else {
					$errors[] = $cI;
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
				} else {
					$errors[] = $cI;
				}
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
}

/**
 * A class that manages the actions
 *
 * @ingroup interface
 * @since 0.2
 * @author Nathan Samson
*/
class actionManager {
	/**
	 * The list of all action The key is the name of the action.
	 * @private
	*/
	var $_actionsList;
	var $_lastActionName;
	var $_lastActionParameters;

	/**
	 * Constructor
	*/
	function actionManager () {
		$this->_actionsList = array ();
		$this->_lastActionName = '';
		$this->_lastActionParameters = array ();
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
	 * @param $permission (string array) The permissions required to run the action
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
	 * @return (bool)
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
				
		$paramString = '';	
		foreach ($params as $k=>$v) {
			$paramString .= '&'.$k.'='.$v;
		}
		
		return 'index.php?action='.$_COOKIE['lastActionName'].$paramString;
	}
	
	function saveLastAction () {
		// clean last action
		foreach ($_COOKIE as $n=>$v) {
			if (substr ($n, 0, strlen ('lastActionParameters_')) == 'lastActionParameters_') {
				setcookie ($n, '');
			}
		}	
	
		setcookie ('lastActionName', $this->_lastActionName);
		foreach ($this->_lastActionParameters as $key=>$value) {
			setcookie ('lastActionParameters_'.$key, $value);
		}
	}
}

?>
