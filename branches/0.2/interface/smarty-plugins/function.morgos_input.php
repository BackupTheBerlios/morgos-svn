<?php
function smarty_function_morgos_input ($params, &$smarty) {
	if (! array_key_exists ('name', $params)) {
		$smarty->trigger_error ("Theme: morgos_input: required parameter name is not given.");
	}

	if (! array_key_exists ('type', $params)) {
		$smarty->trigger_error ("Theme: morgos_input: required parameter type is not given.");
	}	

	$type = $params['type'];	
	$name = $params['name'];
	$value = $_COOKIE['lastActionParameters_'.$name];

	if ($type == 'new_password') {
		$type = 'password';
		$value = null;
	}
	
	if (array_key_exists ('extra', $params)) {
		$extra = $params['extra'];
	} else {
		$extra = null;
	}
	
	return '<input type="'.$type.'" name="'.$name.'" value="'.$value.'" '. $extra .' />';
}
?>