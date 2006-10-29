<?php
function smarty_function_morgos_form_action ($params, &$smarty) {
	if (! array_key_exists ('a', $params)) {
		$smarty->trigger_error ("Theme: morgos_form_action: required parameter a is not given.");
	}
	return '<input type="hidden" name="action" value="'.$params['a'].'" />';
}
?>