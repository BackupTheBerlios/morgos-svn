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
/** \file sqlwrapper.class.php
 * File that take care of the classes for the SQL wrapper
 *
 * @ingroup core database
 * @since 0.2
 * @since 0.3 splitted from databasemanager.functions.php
 * @author Nathan Samson
*/

define ('ORDER_ASC', 'ASC');
define ('ORDER_DESC', 'DESC');

define ('DB_TYPE_STRING', 0);
define ('DB_TYPE_INT', 1);
define ('DB_TYPE_TEXT', 2);
define ('DB_TYPE_ENUM', 3);
//define ('DB_TYPE_STRING', 0);

/**
 * A class that represents a field in some database
 *
 * @ingroup core database
 * @since 0.2
 * @author Nathan Samson
*/
class dbField {
	var $_name;
	var $_type;
	var $_value;
	var $_maxLength;
	var $canBeNull;
	
	function dbField ($name, $type, $maxLength = 0) {
		if (! $this->isValidDBType ($type)) {
			var_dump (new Error ('INVALID_DB_TYPE', $type));
			die ();
		}
		$this->canBeNull = false;
		$this->_type = $type;
		$this->_name = $name;
		$this->setValue (null);
		$this->_maxLength = $maxLength;
	}	
	
	function setValue ($newValue) {
	
		if ($this->getMaxLength () != 0) {
			if (strlen ($newValue) > $this->getMaxLength ()) {
				return new Error ('MAX_LENGTH_EXCEEDED', $this->getName (),
					$this->getMaxLength (), $newValue);
			}
		}	
	
		if ($newValue === null) {
			$this->setValue (''); 			
		} elseif ($this->getType () == DB_TYPE_STRING or 
				$this->getType () == DB_TYPE_TEXT){
			$this->_value = strval ($newValue);
		} elseif ($this->getType () == DB_TYPE_INT) {
			$this->_value = (int) ($newValue);
		} else {
			$this->_value = $newValue;
		}
		
		if ($this->_value === null) {
			morgosBacktrace ();
			die ("Someone nulliefied");
		}
	}
	
	function getValue () {
		return $this->_value;
	}
	
	function getName () {return $this->_name;}
	
	/**
	 * Returns the type of the field
	 *
	 * @return (int) (sort of an enum) 
	 * @since 0.3
	*/
	function getType () {return $this->_type;}
	
	/**
	 * Returns the type of the field as is it used in the db
	 *
	 * @return (string) 
	 * @since 0.3
	*/
	function getDBType () {
		switch ($this->getType ()) {
			case DB_TYPE_INT:				
				return 'int';
			case DB_TYPE_STRING:
				if ($this->getMaxLength () != 0) {
					return 'varchar ('.$this->getMaxLength ().')';
				} else {
					return 'varchar';
				}
			case DB_TYPE_TEXT:
				return 'text';
			/*case DB_TYPE_ENUM:
				return 'ENUM ()';*/
		}
	}
	
	/**
	 * Returns the max length
	 *
	 * @return (int)
	 * @since 0.3
	*/	
	function getMaxLength () {return $this->_maxLength;}
	
	function isValidDBType ($t) {
		return ($t === DB_TYPE_INT or $t === DB_TYPE_STRING or 
			   $t === DB_TYPE_TEXT or $t === DB_TYPE_ENUM); 
	}
	
}

class dbEnumField extends dbField {
	var $_posValues;
	
	function dbEnumField ($name, $type, $value) {
		parent::dbField ($name, $type);
		$this->_posValues = array ();
		for ($i=2;$i<func_num_args ();$i++) {
			$this->_posValues[] = func_get_arg ($i);
		}
	}
	
	function getDBType () {
		/*$t = 'ENUM (\'';
		$values = implode ('\', \'', $this->_posValues);
		$t .=$values.'\')';*/
		$longest = 0;
		foreach ($this->_posValues as $val)  {
			if (strlen ($val) > $longest) {
				$longes = strlen ($val);
			}
		}
		return 'char ('.$longes.')';
	}

}

/**
 * A class that represents a generic join field in a table
 * @ingroup core database
 * @since 0.3
 * @author Nathan Samson
*/
class genericJoinField {
	var $_otherTable;
	var $_otherField;
	var $_name;
	var $_dbField;

	function genericJoinField ($name, $otherTable, $otherField, &$dbField) {
		$this->_name = $name;
		$this->_otherTable = $otherTable;
		$this->_otherField = $otherField;
		$this->_dbField = &$dbField;
	}
	
	function getOtherTable () {return $this->_otherTable;}
	function getOtherField () {return $this->_otherField;}
	function getName () {return $this->_name;}
	function getValue () {return $this->_dbField->getValue ();}
	function getDBField () {return $this->_dbField;}
}

/**
 * A class that represents a one-to-multiple join field in a table
 * @ingroup core database
 * @since 0.3
 * @author Nathan Samson
*/
class oneToMultipleJoinField extends genericJoinField {
	function oneToMultipleJoinField ($name, $otherTable, $otherField, $dbField) {
		parent::genericJoinField ($name, $otherTable, $otherField, $dbField);
	} 
}

/**
 * A class that represents a multiple-to-one join field in a table
 * @ingroup core database
 * @since 0.3
 * @author Nathan Samson
*/
class MultipleToOneJoinField extends genericJoinField {
	function multipleToOneJoinField ($name, $otherTable, $otherField, $dbField) {
		parent::genericJoinField ($name, $otherTable, $otherField, $dbField);
	} 
}

/**
 * A class that represents a one-to-one join field in a table
 * @ingroup core database
 * @since 0.3
 * @author Nathan Samson
*/
class oneToOneJoinField extends genericJoinField {
	function oneToOneJoinField ($name, $otherTable, $otherField, $dbField) {
		parent::genericJoinField ($name, $otherTable, $otherField, $dbField);
	} 
}

/**
 * A class that represents a multiple-to-multiple join field in a table
 * @ingroup core database
 * @since 0.3
 * @author Nathan Samson
*/
class MultipleToMultipleJoinField extends genericJoinField {
	var $_linkTable;	
	
	function MultipleToMultipleJoinField ($name, $otherTable, $otherField, $dbField, 
			$linkTable) {
		$this->_linkTable = $linkTable;
		parent::genericJoinField ($name, $otherTable, $otherField, $dbField);
		
	} 
	
	function getLinkTable () {
		return $this->_linkTable;
	}
	
	// Hardcoded for now
	function getOtherTableDBType () {
		return 'int';
	}
}

/**
 * A generic class that represents Where clauses in SQL statements
 *
 * @ingroup core databease
 * @since 0.3
 * @author Nathan Samson
*/
class WhereClause {
	var $_operator;
	var $_field;
	var $_value;

	function WhereClause ($field, $value, $operator) {
		$this->_field = $field;
		$this->_value = $value;
		if ($this->isValidOperator ($operator)) {
			$this->_operator = $operator;
		} else {
			$this->_operator = '=';
		}
	}
	
	function getSQLString () {
		return $this->getField().$this->getOperator().'\''.$this->getValue ().'\' ';
	}
	
	function getValue () {
		return (string) $this->_value;
	}
	
	function getField () {
		return $this->_field;
	}
	
	function getOperator () {
		switch ($this->_operator) {
			case 'LIKE':
				return ' LIKE ';
			case 'BETWEEN':
				return ' BETWEEN ';
			default:
				return $this->_operator;	
		}
	}
	
	function isValidOperator ($op) {
		switch ($op) {
			case '='      :
			case '>='     :
			case '<='     :
			case '>'      :
			case '<'      :
			case '<>'     :
			case 'LIKE'   :
			case 'BETWEEN':
				return true;
			default:
				return false;
		}
	}
}

/**
 * A Database table object. All tables that are stored in the database must be derived 
 * from this one.
 * 
 * @ingroup core database
 * @since 0.2
 * @since 0.3 renamed to DBTableObject, previously it was databaseObject
 * @author Nathan Samson
*/
class DBTableObject {
	/**
	 * A mixed array of all basic fields with their values
	 * @private 
	*/
	var $_basicFields;
	/**
	 * A mixed array of all extra fields with their values
	 * @private
	*/
	var $_extraFields;
	/**
	 * The databasemodule object
	 * @protected
	*/
	var $_db;
	/**
	 * The name of the database field for the ID
	 * @private
	*/
	var $_IDName;
	/**
	 * The creator of the object
	 * @private
	*/
	var $_creator;
	/**
	 * The different joins of this table
	 * @since 0.3
	 * @private
	*/
	var $_joins;
	
	/**
	 * The constructor
	 *
	 * @param $db (databaseModule)
	 * @param $basicFields (object option array)
	 * @param $tableName (string)
	 * @param $IDName (int)
	 * @param $creator (object) default null
	 * @param $extraFields (object option array) default array ()
	 * @param $joins (object genericJoind array) default array ()
	*/
	function DBTableObject (&$db, $basicFields, $tableName, $IDName, &$creator = null, 
			$extraFields = array (), $joins = array ()) {
		$this->_db = &$db;
		
		$this->setIDName ($IDName);
		$this->setCreator ($creator);
		$this->setTableName ($tableName);
		
		$this->setExtraFields ($extraFields);
		$this->setBasicFields ($basicFields);
		$this->setJoins ($joins);
		
		$this->initEmpty ();
	}	
	
	/*Public initters*/	
	
	/**
	 * Initializes the object with an ID
	 *
	 * @param $ID (int)
	 * @public
	*/
	function initFromDatabaseID ($ID) {
		if (! is_numeric ($ID)) {
			return new Error ('DATABASEOBJECT_SQL_INJECTION_ATTACK_FAILED', 
				__FILE__,__LINE__);
		}
		$prefix = $this->_db->getPrefix ();
		$tableName = $this->getTableName ();
		$IDName = $this->getIDName ();
		$sql = "SELECT * FROM $prefix$tableName WHERE $IDName='$ID'";
		$q = $this->_db->query ($sql);
		if (! isError ($q)) {
			if ($this->_db->numRows ($q) > 0) {
				$row = $this->_db->fetchArray ($q);
				$this->initFromArray ($row);
				$this->setField ('ID', $row[$this->getIDName ()]);
			} else {
				return new Error ('DATABASEOBJECT_ID_NOT_FOUND');
			}
		} else {
			return $q;
		}
	}
	
	/**
	 * Initializes the object from an array
	 * @warning If a key isn't a valid field, an error will be generated and the object
	 * 		will be inited empty again.
	 *
	 * @param $array (mixed array) The keys are the options.
	 * @public
	*/
	function initFromArray ($array) {
		if (! is_array ($array)) {
			return new Error ('PAREMTER_WRONG_PARAMETER', $array);
		}
		$pVal = $this->getFieldValue ('ID');
		$this->initEmpty ();
		$allFields = $this->getAllFields ();
		foreach ($allFields as $dbField) {
			$name = $dbField->getName ();
			if (array_key_exists ($name, $array)) {
				$this->setField ($name, $array[$name]);
			} else {
				if ((! $dbField->canBeNull) and 
					($dbField->getName () != $this->getIDName ())) {
					$this->initEmpty ();
					return new Error ('DATABASEOBJECT_KEY_NOT_EXISTS', $name);
				}
			}
		}
		if ($this->getFieldValue ('ID') !== $pVal) { 
		//	var_dump ($this->getFieldValue ('ID'));
		}
	}
	
	/*Public functions*/
	
	/**
	 * Gets the current value of a field.
	 *
	 * @param $name (string) the name of the field
	 * @public
	 * @return (mixed)
	*/
	function getFieldValue ($name) {
		$field = $this->getField ($name);
		if (! isError ($field)) {
			return $field->getValue ();
		} else {
			return $field;
		}
	}
	
	/**
	 * Returns the field object. 
	 * @param $name (string )
	 * @public
	 * @return (object dbField)
	*/
	function getField ($name) {
		$allFields = $this->getAllFields ();
		if (array_key_exists ($name, $allFields)) {
			return $allFields[$name];
		} elseif ($name == 'ID') {
			return $allFields[$this->getIDName ()];
		} else {
			return new Error ('DATABASEOBJECT_OPTION_DOES_NOT_EXISTS', $name);
		}
	}

	
	/**
	 * Sets the value of an field
	 * @warning all values are converted to a string.
	 *
	 * @param $name (string) the name of the field
	 * @param $value (mixed) the new value
	 * @public
	*/
	function setField ($name, $value) {
		$allExtraFields = $this->getExtraFields ();
		$allBasicFields = $this->getBasicFields ();
		if (array_key_exists ($name, $allExtraFields)) {
			$this->_extraFields[$name]->setValue ($value);
		} elseif (array_key_exists ($name, $allBasicFields)) {
			$this->_basicFields[$name]->setValue ($value);
		} elseif ($name == 'ID') {
			$this->_basicFields[$this->getIDName ()]->setValue ($value);
		} else {
			return new Error ('DATABASEOBJECT_OPTION_DOES_NOT_EXISTS', $name);
		}
	}	
	
	/**
	 * Updates all values from an array.
	 *
	 * @param $array (mixed array) The keys are the fields names, 
	 *  values are the new values.
	 * @public
	*/
	function updateFromArray ($array) {
		$allFields = $this->getAllFields ();
		foreach ($allFields as $option => $value) {
			if (array_key_exists ($option, $array)) {
				$this->setField ($option, $array[$option]);
			}
		}
	}
	
	/**
	 * Checks that the current object is stored into the database.
	 *
	 * @public
	 * @return (bool)
	*/
	function isInDatabase () {
		if ($this->getFieldValue ('ID') < 0) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * Insert the object into the database.
	 *	 
	 * @public
	*/
	function addToDatabase () {
		if (! $this->isInDatabase ()) {
			$prefix = $this->_db->getPrefix ();
			$tableName = $this->getTableName ();
			//$IDName = $this->getIDName ();
			$sql = "INSERT into $prefix$tableName (";
			$allFields = $this->getAllFields ();
			foreach ($allFields as $dbField) {				
				$name = $dbField->getName ();
				if ($name != $this->getIDName ()) {
					$sql.= "$name,";
				}
			}
			$sql[strlen ($sql)-1] = ')'; // remove latest , with )			
			$sql .= ' VALUES (';
			foreach ($allFields as $dbField) {
				if ($dbField->getName () != $this->getIDName ()) {
					$value = $this->_db->escapeString ($dbField->getValue ());
					$sql.= "'$value',";
				}
			}
			$sql[strlen ($sql)-1] = ')'; // remove latest , with )	
			$q = $this->_db->query ($sql);			
			if (! isError ($q)) {
				$ID = $this->_db->latestInsertID ($q);
				$this->setField ('ID', $ID);
			} else {
				return $q;
			}
		} else {
			return new Error ('DATABASEOBJECT_ALREADY_IN_DATABASE');
		}
	}

	/**
	 * Removes the object form the database.
	 *
	 * @public
	*/	
	function removeFromDatabase () {
		if ($this->isInDatabase ()) {
			$prefix = $this->_db->getPrefix ();
			$tableName = $this->getTableName ();
			$IDName = $this->getIDName ();
			$ID = $this->getID ();
			if (! is_numeric ($ID)) {
				return new Error ('DATABASEOBJECT_SQL_INJECTION_ATTACK_FAILED', 
					__FILE__,__LINE__);
			}
			$sql = "DELETE FROM $prefix$tableName WHERE $IDName='$ID'";
			$q = $this->_db->query ($sql);
			if (! isError ($q)) {
				$this->setField ('ID', -1);
			} else {
				return $q;
			}
		} else {
			return new Error ('DATABASEOBJECT_NOT_IN_DATABASE');
		}
	}
	
	/**
	 * Update the data to the database.
	 * @public
	*/
	function updateToDatabase () {
		$updates = '';
		foreach ($this->getAllFields () as $field) {
		 	if (! empty ($updates)) {
		 		$updates .= ', ';
		 	}
			$updates .= $field->getName ();
			$updates .= '=';
			$updates .= '\''.$field->getValue ().'\' '; 
		}
		$fullTableName = $this->getFullTableName (); 
		$IDName = $this->getIDName ();
		$ID = $this->getID ();
		$sql = "UPDATE $fullTableName SET $updates WHERE $IDName='$ID'";
		$a = $this->_db->query ($sql);
		return $a;
	}
	
	/**
	 * @since 0.3 
	*/
	function getAllChildTables ($joinFieldName, $orderField = null, $order = ORDER_ASC, 
			$extraWhere = array (), $fields = '*') {
		$join = $this->getJoin ($joinFieldName);
		if (! isError ($join)) {
			if (is_a ($join, 'oneToMultipleJoinField')) {
				$table = $join->getOtherTable ();
				$otherField = $join->getOtherField ();
				$value = $join->getValue ();
				$sql = "SELECT $fields FROM $table WHERE $otherField='$value'";
				
				if ($extraWhere != array ()) {
					$sql .= " AND ";
					$whereSQLA = array ();
					foreach ($extraWhere as $s) {
						//var_dump ( $s->getSQLString ());
						$whereSQLA[] = $s->getSQLString ();
					}
					$sql .= implode ('AND', $whereSQLA);
				}				
				
				if ($orderField != null) {
					$sql .= " ORDER BY $orderField $order";
				}
				$q = $this->_db->query ($sql);
				if (! isError ($q)) {
					$rows = array ();
					while ($row = $this->_db->fetchArray ($q)) {
						$rows[] = $row;
					}
					return $rows;
				} else {
					return $q;
				}
			} else {
			}
		} else {
			return $join;
		}
	}
	
	/**
	 * @since 0.3 
	*/
	function getAllParentTables ($joinFieldName) {
	}

	/**
	 * @since 0.3 
	*/
	function getSideTable ($joinFieldName) {
		$field = $this->getField ($joinFieldName);
		if (! isError ($field)) {
			if (is_a ($field, 'oneToOneJoinField')) {
				$table = $field->getOtherTable ();
				$otherField = $field->getOtherField ();
				$value = $field->getValue ();
				$sql = "SELECT * FROM $table WHERE $field=$value";
				$q = $this->_db->query ($sql);
				return $this->_db->fetchArray ($q);
			}
		} else {
			return $field;
		}
	}
	
	/**
	 * Gets the ID of the object.
	 *
	 * @return (int)
	 * @public
	*/	
	function getID () {return $this->getFieldValue ('ID');}
	
	/*Protected functions*/
	
	/**
	 * Sets the fieldname of the ID
	 *
	 * @param $name (string) fieldname
	 * @protected
	*/
	function setIDName ($name) {
		$this->_IDName = $name;
	}
	/**
	 * Returns the fieldname of the ID value
	 *
	 * @protected
	 * @return (string)
	*/
	function getIDName () {return $this->_IDName;}
	
	/**
	 * Sets the tablename where the data should be stored (without dbprefix)
	 *
	 * @param $name (string) tablename (without prefix)
	 * @protected
	*/
	function setTableName ($name)	 {
		$this->_tableName = $name;
	}
	
	/**
	 * Returns the tablename where the object is stored (without prefix)
	 *
	 * @protected
	 * @return (string)
	*/
	function getTableName () {return $this->_tableName;}
	
	/**
	 * Returns the full tablename where the object is stored (with prefix)
	 *
	 * @protected
	 * @return (string)
	*/
	function getFullTableName () {
		return $this->_db->getPrefix ().$this->getTableName ();
	}
	
	/**
	 * Set the creator of the object.
	 *
	 * @param $creator (object)
	 * @protected
	*/
	function setCreator (&$creator) {
		$this->_creator = &$creator;
	}
	
	/**
	 * Returns the creator of the object
	 *
	 * @protected
	 * @return (object)
	*/
	function getCreator () {return $this->_creator; }
	
	/**
	 * Set all extra fields that should be stored in the db.
	 *
	 * @param $array (object dbField array)
	 * @protected
	*/
	function setExtraFields ($array) {
		$this->_extraFields = array ();
		foreach ($array as $e) {
			$this->_extraFields[$e->getName ()] = $e;
		}
	}
	
	/**
	 * Returns all extra fields
	 *
	 * @protected
	 * @return (mixed array) the keys are the fieldname. Values are the current values.
	*/
	function getExtraFields () {return $this->_extraFields;}
	
	/**
	 * Set all basic fields (without ID) that should be stored in the DB.
	 *
	 * @param $array (object dbField)
	 * @protected
	*/
	function setBasicFields ($array) {
		$this->_basicFields = array ();
		foreach ($array as $e) {
			$this->_basicFields[$e->getName ()] = $e;
		}
		if (! array_key_exists ($this->getIDName (), $this->_basicFields)) {
			$this->_basicFields[$this->getIDName ()] = 
				new dbField ($this->getIDName (), DB_TYPE_INT, 11);
		}
	}
	
	/**
	 * Returns all basic fields
	 *
	 * @protected
	 * @return (mixed array) the keys are the fieldnames. Values are the current values.
	*/
	function getBasicFields () {return $this->_basicFields;}
	
	/**
	 * Returns an array of all fields. (without ID)
	 *
	 * @protected
	 * @return (mixed array) the keys are the fieldnames. Values are the current values.
	*/
	function getAllFields () {
		return array_merge ($this->getExtraFields (), $this->getBasicFields ()); 
	}
	
	function getAllJoinFields () {
		return $this->_joins;
	}
	
	/**
	 * Set the join fields.
	 *
	 * @param $joins (object genericJoin array) a list of the joins
	 * @protected
	*/
	function setJoins ($joins) {
		$this->_joins = array ();
		foreach ($joins as $j) {
			$this->_joins[$j->getName ()] = $j;
		}
	}
	
	/**
	 * Return a specific join field.
	 *
	 * @param $name (string)
	 * @public
	 * @return (object genericJoin)
	*/
	function getJoin ($name) {
		if (array_key_exists ($name, $this->_joins)) {
			return $this->_joins[$name];
		} else {
			return new Error ('FIELD_JOIN_NOT_FOUND', $this->getTableName (), $name);
		}
	}
	
	/**
	 * Empty initializer.
	 *	 
	 * @protected
	*/	
	function initEmpty () {
		$this->setField ('ID', -1);
		$allFields = $this->getAllFields ();
		foreach ($allFields as $name => $value) {
			if ($name != $this->getIDName ()) {
				$this->setField ($name, null);
			}
		}
	}
}

/**
 * A class that can hold information about several tables that should be grouped.
 * @since 0.3
 * @author Nathan Samson
*/
class DBTableManager {
	/**
	 * An array of all the tables that this manages.
	 * @private
	*/
	var $_tableList;
	/**
	 * A  multideminsional array for extra joinst for all tables
	 * @private
	*/
	var $_extraJoinList;
	/**
	 * The DBModule
	 * @protected
	*/
	var $_db;

	/**
	 * The constructor
	 *
	 * @param $db (object DBManager) 
	 * @param $table (string)... The tablename (without prefix),
	 * @param $object (string)... The object name
	 *	Repeat this last 2 params for each table.
	*/
	function DBTableManager (&$db, $table, $object) {
		$this->_db = &$db;
		$this->_tableList = array ();
		$this->_extraJoinList = array ();
		for ($i = 1; $i<func_num_args (); $i=$i+2) {
			$table = func_get_arg ($i);
			$object = func_get_arg ($i+1);
			$this->_tableList[$table] = $object;
			$this->_extraJoinList[$table] = array ();
		}
	}

	/**
	 * Return all tables.
	 *
	 * @public
	 * @return (string array)
	*/
	function getAllTables () {
		$t = array ();
		foreach ($this->_tableList as $tName=>$tClass) { 
			$t[] = $tName;
		}
		return $t;
	}
	
	/**
	 * Install a table.
	 *
	 * @param $tableName (without prefix)
	 * @public
	*/
	function installTable ($tableName) {
		if ($this->managesTable ($tableName)) {		
			$oName = $this->_tableList[$tableName];
			$sql = 'CREATE TABLE '. $this->_db->getPrefix().$tableName . ' (';
			$o = new $oName ($this->_db, $this);
			foreach ($o->getAllFields () as $field) {
				$sql .= $field->getName ();
				if ($field->getName () == $o->getIDName ()) {
					if ($this->_db->getType () == 'PostgreSQL') {
						$sql .= ' serial ';
					} else {
						$sql .= ' int AUTO_INCREMENT ';
					}
				} else {
					$sql .= ' '.$field->getDBType ();
				}
				if (! $field->canBeNull) {
					$sql .= ' NOT NULL';
				}
				$sql .= ", \n";
			}
			$sql .= 'PRIMARY KEY ('.$o->getIDName ().')';
			$sql .= ')';
			$a = $this->_db->query ($sql);
			
			foreach ($o->getAllJoinFields () as $join) {
				if (get_class ($join) == 'MultipleToMultipleJoinField') {
					$fullLinkTable = $this->_db->getPrefix().$join->getLinkTable ();
					if (! $this->_db->tableExists ($fullLinkTable)) {
						$thisField = $join->getDBField ();
						$sql = 'CREATE TABLE '.$fullLinkTable.' (';
						$sql .= $join->getOtherField ().' '.
								$join->getOtherTableDBType ();
						/*var_dump ($join);
						var_dump ($thisField);*/
						$sql .= ', '.$thisField->getName ().' '.
							$thisField->getDBType ();
						$sql .= ')';
						$this->_db->query ($sql);
					}
				}
			}
		}
	}
	
	/**
	 * Install all tables
	 * @public
	*/
	function installAllTables () {
		foreach ($this->getAllTables () as $t) {
			$this->installTable ($t);
		}
	}
	
	/**
	 * Checks if all tables are installed.
	 * @public
	 * @return (bool)
	*/
	function isInstalled () {
		foreach ($this->getAllTables () as $t) {
			if (! $this->isTableInstalled ($t)) {
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Checks if a table is installed.
	 * @public
	 * @param $tableName (string) the name of the table (with or without prefix)
	 * @return (bool)
	*/
	function isTableInstalled ($tableName) {
		return $this->_db->tableExists ($tableName);
	}
	
	/**
	 * Checks that this objects manages a table.
	 *
	 * @param $tName (string) table Name (Without prefix)
	 * @public
	 * @return (bool)
	*/
	function managesTable ($tName) {
		return array_key_exists ($tName, $this->_tableList);
	}
	
	/**
	 * Returns a new object for a table.
	 *
	 * @param $tableName (string) The table name (Without prefix)
	 * @public
	 * @return (object DBTableObject)
	*/
	function createObject ($tableName) {
		if ($this->managesTable ($tableName)) {
			$oName = $this->_tableList[$tableName];
			return new $oName ($this->_db, $this,
				$this->getExtraFieldsForTable ($tableName));
		} else {
			return new Error ('DONT_MANAGE_THIS_TABLE', $tableName);
		}
	} 
	
	/**
	 * Adds a new row to the table.
	 *
	 * @param $newRow (object dbField)
	 * @param $tableName (string) Guess what this is?
	*/	
	function addRowToTable ($newRow, $tableName) {
		if ($this->managesTable ($tableName)) {
			return $newRow->addToDatabase ();
		} else {
			return new Error ('DONT_MANAGE_THIS_TABLE', $tableName);
		}
	}
	
	/**
	 * Returns all  rows for a table.
	 *
	 * @param $tableName (string)
	*/
	function getAllRowsFromTable ($tableName) {
		if ($this->managesTable ($tableName)) {
			$prefix = $this->_db->getPrefix ();
			$SQL = "SELECT * FROM {$prefix}{$tableName}";
			$q = $this->_db->query ($SQL);
			if (! isError ($q)) {
				$rows = array ();
				while ($row = $this->_db->fetchArray ($q)) {
					$rowObject = $this->createObject ($tableName);
					$rowObject->initFromArray ($row);
					$rows[] = $rowObject;
				}
				return $rows;
			} else {
				return $q;
			}
		} else {
			return new Error ('DONT_MANAGE_THIS_TABLE', $tableName);
		}
	}
	
	/**
	 * Adds an extra option for a table.
	 *
	 * @param $tableName (string) The table name
	 * @param $newField (object dbField) The new field
	 * @public
	*/	
	function addExtraFieldForTable ($tableName, $newField) {
		if ($this->managesTable ($tableName)) {
			$curFields = $this->getAllFieldsForTable ($tableName);
			if (! isError ($curFields)) {
				if (! array_key_exists ($newField->getName (), $curFields)) {
					$newField->canBeNull = true;
					$r = $this->_db->addNewField ($newField, 
							$this->_db->prefix.$tableName);
					if (isError ($r)) {
						return $r;
					}
				} else {
					return new Error ('TABLEMANAGER_OPTION_FORPAGE_EXISTS', 
						$tableName, $newOption->getName ());
				}
			} else {
				return $curFields;
			}			
		} else {
			return new Error ('DONT_MANAGE_THIS_TABLE', $tableName);
		}
	}
	
	/**
	 * Returns all extra fields for a table.
	 *
	 * @todo This function uses a quite hackish method, think of another.  
	 * @param $tableName (string) The table name
	 * @public
	 * @return (array dbField) 
	*/
	function getExtraFieldsForTable ($tableName) {
		if ($this->managesTable ($tableName)) {
			$oName = $this->_tableList[$tableName];
			$o = new $oName ($this->_db, $this);

			$filter = array ();
			foreach ($o->getBasicFields () as $fI) {
				$filter[] = $fI->getName ();
			}
			//var_dump ($filter);
			return $this->_db->getAlldbFields (
					$this->_db->getPrefix ().$tableName, $filter);		
		} else {
			return new Error ('DONT_MANAGE_THIS_TABLE', $tableName);
		}
	}
	
	/**
	 * Returns all fields for a table.
	 *
	 * @todo This function uses a quite hackish method, think of another.  
	 * @param $tableName (string) The table name
	 * @public
	 * @return (array dbField) 
	*/
	function getAllFieldsForTable ($tableName) {
		if ($this->managesTable ($tableName)) {
			$oName = $this->_tableList[$tableName];
			$o = new $oName ($this->_db, $this);

			return $this->_db->getAlldbFields (
					$this->_db->getPrefix ().$tableName);		
		} else {
			return new Error ('DONT_MANAGE_THIS_TABLE', $tableName);
		}
	}
	
	/**
	 * Returns all extra JoinFields for a table
	 *  
	 * @param $tableName (string) The table name
	 * @public
	 * @return (array dbGenericJoinField) 
	*/
	function getExtraJoinFieldsForTable ($tableName) {
		if ($this->managesTable ($tableName)) {
			return $this->_extraJoinList[$tableName];			
		} else {
			return new Error ('DONT_MANAGE_THIS_TABLE', $tableName);
		}
	}
	
	/**
	 * Adds an extra join field fot a table
	 *
	 * @param $tableName (string) The table name
	 * @param $newJoin (genericDBJoinField) The newly added join
	 * @public
	*/
	function addExtraJoinFieldForTable ($tableName, $newJoin) {
		if ($this->managesTable ($tableName)) {
			return $this->_extraJoinList[$tableName][] = $newJoin;			
		} else {
			return new Error ('DONT_MANAGE_THIS_TABLE', $tableName);
		}
	}
}

?>
