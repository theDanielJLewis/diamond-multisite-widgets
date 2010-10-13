<?php
function diamond_arr_to_str($arg) {
	$ret = '';	
	if (!$arg || $arg == '')
		return $ret;	
	foreach($arg AS $a)
		$ret.=$a;
	return $ret;
}
?>