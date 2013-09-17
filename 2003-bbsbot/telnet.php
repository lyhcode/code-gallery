#!/usr/bin/php -q
<?php
/* 
 *  PHP-Telnet RoBot : The BBS Client for Programmable and ..Fun
 * 
 *  作者： LYH (lightcool2003@yahoo.com.tw)
 *         http://24php.idv.tw/
 * 
 *  初版： 08/18/2003
 * 
 */

/* 本程式包含之所有程式碼以 GNU GPL v2 發行 */
/* 關於授權條款請詳閱目錄中 LICENSE文件說明 */

/* 使用方法請先閱讀README */

/* 最重要的設定檔 */
require_once("config.php");

/* 命令列參數處理 */
require_once("libargv.php");

/* 通用函式庫 */
require_once("libterm.php");  //TELNET特殊字元定義
require_once("libsock.php");  //BBS連線與資料傳輸
/* 停用ncui模組, 08-23-2003
require_once("libncui.php");  //文字模式視窗介面繪製
*/
 require_once("libecho.php");  //特殊用途的文字輸出
require_once("libereg.php");  //特殊用途的字串比對

/* 金撲克梭哈遊戲模組 */
include_once("mod_gp.php");

/* 銀行自動轉帳模組 */
include_once("mod_tran.php");

/* 銀行自動存款模組 */
include_once("mod_save.php");



/* 主程式開始 */

$stdin = fopen('php://stdin', 'r');  /* 讀取標準輸入用的Handle */
stream_set_blocking($stdin, 0);      /* 設為non-block模式 */

srand(make_seed()); //亂數種子
mt_srand(make_seed());


/* 程式啟動的時間 */
$time_begin = time();

/* 輸出歡迎訊息 */
echo_file("welcome.msg");


/* 文字視窗介面建立初始化 */

echo_color(ANSI_ZERO,ANSI_WHITE,"啟動UI圖形介面 (螢幕控制) ...\n");

/*
ui_create();           //啟用ncurses螢幕控制
ui_screen_init();      //初始化ncurses，並繪製背景視窗
ui_check_size();       //檢查行列的寬度，不能小於80*24的標準大小
ui_window_message();   //繪製訊息輸出用的視窗
ui_close();            //清除螢幕回到標準的終端機
ui_doupdate();         //立即更新先前的畫面
ui_close();            //先清除畫面，讓使用者自己啟用
*/

echo_ok("LOAD_UI");

/* ncurses版的訊息輸出功能只是實驗性質的支援 */


/* 開始連線到BBS伺服器 */

echo_color(ANSI_ZERO,ANSI_WHITE,
	   "建立BBS遠端連線 ($BBS_ADDRESS) ...\n");

if ( $telnet = telnet_connect($BBS_ADDRESS, $BBS_PORT) )
{
    echo_ok("TELNET");
}
else
{
    echo_error("TELNET");
    exit(0);
}

/* 處理TELNET的協定 */

echo_color(ANSI_ZERO,ANSI_WHITE,
	   "等待BBS回應訊息 ($BBS_WELCOME) ...\n");

if ( telnet_protocol($telnet, $BBS_WELCOME) )
{
    echo_ok("REPLY");
}
else
{
    echo_error("REPLY");
    exit(0);
}


/* BBS自動登入程序 */

echo_color(ANSI_ZERO,ANSI_WHITE,
	   "啟用BBS自動登入 ($BBS_LOGINID) ...\n");

if ( telnet_login($telnet,
		  $BBS_LOGINID, $BBS_LOGINPW,
		  $BBS_EXPECT, $BBS_REGULAR) )
{
    echo_ok("LOGIN");
}
else
{
    echo_error("LOGIN");
    exit(0);
}

echo_color(ANSI_ZERO,ANSI_WHITE,
	   "檢查BBS外掛模組 (modules) ...\n");
echo_ok("CHKMOD");

/* 金撲克梭哈模組：進入遊戲狀態 */

if (HAVE_MODULE_GP)
{
    echo_color(ANSI_ZERO,ANSI_WHITE,
	       "啟用");
    echo_color(ANSI_HIGH,ANSI_YELLOW,
	       "金撲克");
    echo_color(ANSI_ZERO,ANSI_WHITE,
	       "遊戲模組 (mod_gp) ...\n");
    
    if ( module_gp_init($telnet) )
    {
	echo_ok("MOD_GP");
    }
    else
    {
	echo_error("MOD_GP");
	exit(0);
    }
}


/* 銀行轉帳模組 */

if (HAVE_MODULE_TRAN)
{
    echo_color(ANSI_ZERO,ANSI_WHITE,
	       "啟用銀行自動轉帳模組 (mod_tran) ...\n");
    if ( module_tran_init($telnet) )
    {
	echo_ok("MOD_TRAN");
    }
    else
    {
	echo_error("MOD_TRAN");
	exit(0);
    }
}


/* 銀行存款模組 */

if (HAVE_MODULE_SAVE)
{
    echo_color(ANSI_ZERO,ANSI_WHITE,
	       "啟用銀行自動存款模組 (mod_save) ...\n");
    if ( module_save_init($telnet) )
    {
	echo_ok("MOD_SAVE");
    }
    else
    {
	echo_error("MOD_SAVE");
	exit(0);
    }
}



/* 開始進入迴圈 */

echo "\n";
echo_color(ANSI_HIGH,ANSI_WHITE,
	   "您可以開始輸入指令：\n");
echo_color(ANSI_ZERO,ANSI_WHITE,
	   "輸入'/help'可以查詢指令說明\n");

$is_loop = true;
$is_ncui = false; 
$is_game = true;

/*
ui_doupdate();
ui_close();
*/


echo $PROMPT;
flush();

/* 初始化遊戲時間計時器 */
$game_time       = 0;
$game_time_begin = time();

while ( $is_loop )
{
    /* 讀取按鍵, non-blocking不會延滯 */
    
    if ( $press = fgets($stdin) )
    {
	if ($is_ncui) //UI介面中的按鍵觸發
	{
	    //$is_ncui = ui_key_event($press);
	    
	    if (!$is_ncui)
	    {
		echo "已經關閉UI介面\n";
		echo $PROMPT;
		flush();
	    }
	}
	else
	{
	    switch ( $press )
	    {
	     case "/quit\n":
		echo "\n";
		flush();
		$is_loop = false; //離開程序
		break;
	     case "/ncui\n":
		echo "切換至UI介面\n";
		//ui_doupdate();
		//$is_ncui = true;
		break;
	     case "/help\n":
		show_help();
		break;
	     case "/date\n":
		show_date();
		break;
	     case "/buff\n":
		show_buffer();
		break;
	     case "/money\n":
		show_money();
		break;
	     case "/play\n":
		$is_game = true;
		echo "開啟遊戲\n";
		break;
	     case "/stop\n":
		echo "關閉遊戲\n";
		$is_game = false;
		break;
	     default:
		echo "指令未定義\n";
		break;
	    }
	    if ( $is_loop && !$is_ncui )
	    {
		echo $PROMPT;
		flush();
	    }
	}
    }
    

    /* 計時器 */
    $time_compare = time()-$time_begin;

    //時間限制
    if ( TIME_LIMIT && ($time_compare>=TIME_LIMIT) )
    {
	echo "已經超過限制時間\n";
	$is_loop = false;
    }

    /* 遊戲時間計時器 */
    if ( $is_game )
    {
	if ( time() - $game_time_begin >= 1 )
	{
	    $game_time += (time() - $game_time_begin);
	    $game_time_begin = time(); //重新設定時間
	}
    }
    else
    {
	$game_time_begin = time(); //重新設定時間
    }
    
    /*
     if ( ($time_compare % 60)==0 )
     {
     telnet_write($telnet, " ");
     }
     */
    
    /* 迴圈計數器 */
    $loop_n ++;

    /* 金撲克梭哈模組：遊戲主程式 */
    if ( HAVE_MODULE_GP && $is_game )
    {
	module_gp_main($telnet);
    }
    
    /* 執行銀行轉帳模組 */
    if ( HAVE_MODULE_TRAN )
    {
	module_tran_main($telnet);
    }
    
    /* 執行銀行存款模組 */
    if ( HAVE_MODULE_SAVE )
    {
	module_save_main($telnet);
    }
    
    usleep(1);
}

/* 重繪終端機的畫面 */
/*
ui_doupdate();
ui_close();
*/

if (HAVE_MODULE_GP)
{
    /* 顯示賺到的錢 */
    show_money();
    echo "\n";
}


/* 終止程序 */

fclose($stdin); //關閉標準輸入的讀取


/* 將緩衝區剩餘的內容輸出(除錯功能) */
if (DEBUG_MODE)
{
    echo "\nDUMP BUUFFER\n";
    echo ">----------------------------------------------<\n";
    echo str_replace(chr(27),"^",$buff_socket); //force <-- not good
    echo "\n>----------------------------------------------<\n";
}

telnet_close($telnet); //結束連線

usleep(100000);
echo_color(ANSI_ZERO,ANSI_WHITE,"結束程序!\n");

echo_ok("DOWN");
echo "\n";




function show_buffer()
{
    global $buff_socket;
    echo "\nDUMP BUUFFER\n";
    echo ">----------------------------------------------<\n";
    echo str_replace(chr(27),"^",$buff_socket); //force <-- not good
    echo "\n>----------------------------------------------<\n";
}

function show_date()
{
    global $time_begin;
    echo "建立： ".date("D M j G:i:s T Y", $time_begin);
    echo "\n";
    echo "現在： ".date("D M j G:i:s T Y");
    echo "\n";
    echo
      "經過： ".(intval((time()-$time_begin)/60))."分鐘",
      "又".((time()-$time_begin)%60)."秒(".(time()-$time_begin)."秒)\n";
    echo
      "剩餘： ".(TIME_LIMIT-(time()-$time_begin)).
      "秒(最大".TIME_LIMIT."秒)\n";

    //if (DEBUG_MODE)
    {
	global $loop_n;
	echo "迴圈： $loop_n(".($loop_n/(time()-$time_begin))."/秒)\n";
    }
}

function show_help()
{
    echo "/help\t顯示求助說明\n";
    echo "/ncui\t切換至UI介面\n";
    echo "/quit\t結束程式\n";
    echo "/date\t查詢時間\n";
    echo "/buff\t查看緩衝區\n";
    echo "/play\t開始遊戲\n";
    echo "/stop\t暫停遊戲\n";
    echo "/money\t顯示獲得的錢\n";
}

function show_money()
{
    global $game_time;
    echo "遊戲時間經過",$game_time,"秒\n";
    echo "已經得到 ",echo_read("money")," 元\n";
    echo "平均",(echo_read("money")/$game_time),"元/秒\n";
    echo "預期每分鐘賺進",(echo_read("money")/$game_time*60),"元\n";
    echo "共計玩",echo_read("rounds"),"次, 贏",echo_read("wins"),"次\n";
    echo "賺到錢的機率",(echo_read("wins")/echo_read("rounds")*100),"%\n";
    if (echo_read("wins")>0)
      echo "每",($game_time/echo_read("wins")),"秒可贏１場\n";
    echo "每分鐘可贏",(echo_read("wins")/$game_time*60),"場\n";
    if (echo_read("wins")>0)
      echo "平均每次勝利獲得",(echo_read("money")/echo_read("wins")),"元\n";
    echo "上次勝利得到",(echo_read("advantage")),"元\n";
}

function make_seed()
{
    list($usec, $sec) = explode(' ', microtime());
    return (float) $sec + ((float) $usec * 100000);
}

?>
