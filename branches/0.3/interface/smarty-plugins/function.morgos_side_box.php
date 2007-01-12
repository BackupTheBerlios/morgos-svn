<?php
function smarty_function_morgos_side_box ($params, &$smarty) {
	if (array_key_exists ('BoxTitle', $params)) {
		$title = trim ($params['BoxTitle']);
	} elseif (array_key_exists ('EvalBoxTitle', $params)) {
		$title = trim ($params['EvalBoxTitle']);
		$valArray['var'] = '{'.$title.'}';
		$valArray['assign'] = null;
		include_once (SMARTY_CORE_DIR.'../plugins/function.eval.php');
		$title = smarty_function_eval ($valArray, $smarty); // not so clean
	} else {
		$smarty->trigger_error ("Theme: error");
	}
	
	if (! array_key_exists ('BoxContentFile', $params)) {
		$smarty->trigger_error ("Theme: error");
	}
	
	$smarty->assign ('BoxContent', $smarty->fetch ($params['BoxContentFile']));
	$smarty->assign ('BoxTitle', $title);	
	
	$SideBox = $smarty->fetch ('sidebox.tpl');
	return $SideBox;
}
?>