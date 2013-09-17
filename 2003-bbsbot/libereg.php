<?php
/* -------------------------- */
/*                            */
/* PHP-Telnet Regular Library */
/*                            */
/* -------------------------- */


/* 會取出字串(裁剪)的樣式比較 */
function regular_reduce(&$buffer, $string)
{
    if ( eregi($string.'(.*)', $buffer, $match) )
    {                               
	$buffer = $match[1];
	return true;
    }
    return false;
}

/* 測試字串是否存在 */
function regular_query(&$buffer, $string)
{
    if ( eregi($string, $buffer) )
    {
	return true;
    }
    return false;
}

?>

