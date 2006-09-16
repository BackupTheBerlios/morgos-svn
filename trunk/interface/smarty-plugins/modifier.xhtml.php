<?php

function smarty_modifier_xhtml ($string) {
	return htmlentities ($string);
}

?>