<?php
/* --------------------------------- */
/*                                   */
/*    PHP-Telnet : 命令列參數處理    */
/*                                   */
/* --------------------------------- */

/*
 *  使用之前必須先引入config.php 
 */ 


/* 處理參數列的設定 */
$have_error = false;
$have_set   = false;
$have_host  = false;
$module_set = NULL;

foreach ($argv as $key=>$args)
{
    if ($key == 0)
    {
	continue;
    }
    elseif ($have_error)
    {
	break;
    }
    elseif ($have_set)
    {
	$have_set = false;
    }
    elseif ( $argc >= 2 )
    {
	if ( substr($args,0,1) == "-" )
	{
	    switch(substr($args,1))
	    {
	     case "d":
		break;
	     case "u":
		$BBS_LOGINID = $argv[$key+1];
		if (strlen($BBS_LOGINID)==0)
		{
		    $have_error = true;
		}
		$have_set = true;
		break;
	     case "p":
		$BBS_LOGINPW = $argv[$key+1];
		if ($BBS_LOGINPW=="/ask")
		{
		    echo "Password: ".chr(27)."[0;30m";
		    flush();
		    $fp = fopen("php://stdin", r);
		    $BBS_LOGINPW = fgets($fp);
		    fclose($fp);
		    unset($fp);
		    echo chr(27)."[m";
		    $BBS_LOGINPW = str_replace("\n","",$BBS_LOGINPW);
		}
		$have_set = true;
		break;
	     case "m":
		$module_set =  $argv[$key+1];
		if (strlen($module_set)==0)
		{
		    $have_error = true;
		}
		$have_set = true;
		break;
	     default:
		$have_error = true; //錯誤
		break;
	    }
	}
	else
	{
	    if ( ! $have_host)
	    {
		$BBS_ADDRESS = $args;
		$have_host   = true;
	    }
	    else
	    {
		if ( is_numeric($args) && intval($args)>0 )
		{
		    $BBS_PORT = intval($args);
		}
		else
		{
		    $have_error = true;
		}
	    }
	}
    }
    else 
    {
	$have_error = true;
    }
}

if ($have_error || $argc<2 )
{
    echo "Usage: ".$argv[0]." [選項] [主機位址] [埠號]\n";
    echo "\n";
    echo "-d\t保留預設(config.php)\n";
    echo "-u\t帳號名稱\n";
    echo "-p\t設定密碼\n";
    echo "-m\t模組名稱\n";
    echo "\n";
    exit(0);
}

/* 決定啟動的模組 */
switch ( $module_set )
{
 case "mod_gp":
    define("HAVE_MODULE_SET",  true);
    define("HAVE_MODULE_GP",   true);
    define("HAVE_MODULE_TRAN", false);
    define("HAVE_MODULE_SAVE", false);
    break;
 case "mod_tran":
    define("HAVE_MODULE_SET",  true);
    define("HAVE_MODULE_TRAN", true);
    define("HAVE_MODULE_GP",   false);
    define("HAVE_MODULE_SAVE", false);
    break;
 case "mod_save":
    define("HAVE_MODULE_SET",  true);
    define("HAVE_MODULE_TRAN", false);
    define("HAVE_MODULE_GP",   false);
    define("HAVE_MODULE_SAVE", true);
    break;    
 default:
    echo "請設定正確的模組!\n";
    echo "mod_gp\t\t- 梭哈遊戲\n";
    echo "mod_tran\t- 銀行轉帳\n";
    echo "mod_save\t- 銀行存款\n";
    echo "\n";
    exit(0);
    break;
}

unset($have_error);
unset($have_set);
unset($have_host);
unset($module_set);

?>
	
