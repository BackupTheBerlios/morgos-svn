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
			return new Error ('EMPTY_INPUT');
		}
	}
	
	function isGiven ($from)  {
		$fromArray = $this->getFromArray ($from);
		return array_key_exists ($this->_name, $fromArray);
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

class EmailInput extends StringInput {
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
}

/**
 * An locale input class.
 * @ingroup interface
 * @since 0.2
*/
class LocaleInput extends baseInput {
}

/**
 * A password input class. This is used when the user inputs a new password (that should be repeated)
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
			return new Error ('EMPTY_INPUT');
		}
	}
	
	function isGiven ($from)  {
		$fromArray = $this->getFromArray ($from);
		return (array_key_exists ($this->_name.'1', $fromArray) and 
			array_key_exists ($this->_name.'2', $fromArray)) ;
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
	 * Constructor.
	 *
	 * @param $name (string)
	 * @param $method (string) POST or GET
	 * @param $executor (PHP callback)
	 * @param $requiredOptions (string array)
	 * @param $notRequiredOptions (empty array) Not used yet
	*/
	function action ($name, $method, $executor, $requiredOptions, $notRequiredOptions = array ()) {
		$this->_name = $name;
		$this->_method = $method;
		$this->_requiredOptions = $requiredOptions;
		$this->_notRequiredOptions = $notRequiredOptions;
		$this->_executor = $executor;
	}
	
	/**
	 * Executes the action and returns it result.
	 *
	 * @param $params (mixed array) array with the parameters to use, if empty use default source. (GET/POST)
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
					return new Error ('ACTIONMANAGER_REQUIRED_OPTION_NOT_FOUND', $option);
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
					return new Error ('ACTIONMANAGER_REQUIRED_OPTION_NOT_FOUND', $option);
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
	*/	
	function executeAction ($actionName, $var = array ()) {
		if ($this->existsAction ($actionName)) {
			$action = $this->getAction ($actionName);
			if (! isError ($action->getParameters ($var))) {
				$this->_lastActionName = $actionName;
				$this->_lastActionParameters = $action->getParameters ($var);
				return $action->execute ($var);
			} else {
				return $action->getParameters ($var);
			}
			var_dump ($action->getParameters ($var));
			die ();
		} else {
			return new Error ('ACTIONMANAGER_ACTION_NOT_FOUND', $actionName);
		}
	}
	
	/**
	 * Adds an action
	 * @param $action (object action)
	 * @public
	*/
	function addAction ($action) {
		$actionName = $action->getName ();
		if (! $this->existsAction ($actionName)) {
			$this->_actionsList[$actionName] = $action;
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
		
		return 'Location: index.php?action='.$_COOKIE['lastActionName'].$paramString;
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
