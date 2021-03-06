<?php
function smarty_function_t ($params, &$smarty) {
	$localizer = $smarty->get_template_vars ('t');

	if (! array_key_exists ('s', $params)) {
		$smarty->trigger_error ("Theme: morgos_t: required parameter s is not given.");
	}	
	
	$stringParams = array ();
	foreach ($params as $k=>$v) {
		if ($k != 's') {
			$stringParams[] = $v;
		}
	}
	
	return $localizer->translate ($params['s'], $stringParams);
}
?>