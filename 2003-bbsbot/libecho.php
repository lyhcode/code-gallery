<?php
/* ----------------------------- */
/* PHP-Telnet Color Text Library */
/* ----------------------------- */


/* Color */
define("ANSI_BLACK",  30);
define("ANSI_RED",    31);
define("ANSI_GREEN",  32);
define("ANSI_YELLOW", 33);
define("ANSI_BLUE",   34);
define("ANSI_PURPLE", 35);
define("ANSI_CYAN",   36);
define("ANSI_WHITE",  37);
/* Mode */
define("ANSI_ZERO",   0);
define("ANSI_HIGH",   1);
// define("ANSI_BLINK",  5); //不支援閃爍功能
/* ESC */
define("ANSI_ESC", chr(27));

//輸出包含色彩(ANSI控制碼)的文字
function echo_color($mode, $color, $text)
{
    echo
      ANSI_ESC,"[",
      $mode,";",
      $color,"m";
    echo
      $text;
    echo
      ANSI_ESC,"[m";
    return;
}

//輸出文字檔案中所有內容
function echo_file($filename)
{
    $handle = fopen($filename, "r");
    while (!feof($handle))
    {
	$buffer = fgets($handle, 4096);
	echo $buffer;
    }
    fclose($handle);
}

//執行結果正確的訊息
function echo_ok($text)
{
    echo_color(ANSI_HIGH,ANSI_WHITE," [ ");
    echo_color(ANSI_HIGH,ANSI_GREEN,"$text+OK");
    echo_color(ANSI_HIGH,ANSI_WHITE," ]\n");
}

//執行結果失敗的訊息
function echo_error($text)
{
    echo_color(ANSI_HIGH,ANSI_WHITE," [ ");
    echo_color(ANSI_HIGH,ANSI_RED,"$text+ERROR");
    echo_color(ANSI_HIGH,ANSI_WHITE," ]\n");
}

$ECHO_ARRAY = NULL;

//統一將資料寫入索引陣列
function echo_write($name, $string)
{
    global $ECHO_ARRAY;
    $ECHO_ARRAY[$name]=$string;
    return;
}
function echo_read($name)
{
    global $ECHO_ARRAY;
    return($ECHO_ARRAY[$name]);
}
function echo_dump()
{
    global $ECHO_ARRAY;
    print_r($ECHO_ARRAY);
    return;
}

?>
