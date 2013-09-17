<?php
/*
 * PHP-Telnet RoBot
 * 
 *  ========================
 *   銀行自動存款功能模組
 *  ========================
 * 
*/


/* 啟用銀行自動轉帳功能模組 */
//define("HAVE_MODULE_SAVE",   true);
//在config檔設定此項目

/* 超過多少錢才要把銀幣換成金幣 */
define("MONEY_RESERVE", 200000000);
/* 銀幣 -> 金幣 的匯率 */
define("MONEY_RATE", 100050);
/* 銀幣 -> 金幣 的上限 */
define("MONEY_MAX_CONV", 900000000);


/* 模組初始化：進入銀行畫面 */
function module_save_init($conn_sock)
{
    //先把之前的緩衝區都清除乾淨
    telnet_erase();
    //直接進入遊戲畫面
    telnet_write($conn_sock, "x");
    telnet_write($conn_sock, "\nm\nb\n");

    //計時器
    $start_time = time();
    
    while (1)
    {
	telnet_buffer($conn_sock);

	if ( telnet_query("歡迎光臨本銀行") )
	{
	    return(true);
	}
	
	//連線逾時
	if ( (time() - $start_time) > TIME_OUT )
	{
	    echo "模組載入時發生錯誤\n";
	    return(false);
	}
	
    }
    return(false);
}

/* 模組的主要程式碼 */
function module_save_main($conn_sock)
{
    global $is_loop;

    echo "\n";
    
    $query_str = '您現在有銀幣 .?.?33m[ ]*([0-9]*).?.?32m 元';
    if ( !telnet_expect_match($query_str, $match) )
    {
	echo "您無法使用匯款功能！\n";
	exit(0);
    }
    $money_have = intval($match[1]);
    unset($match);
    
    $query_str = '金幣 .?.?33m[ ]*([0-9]*).?.?32m 元';
      //'金幣 .?.?33m[ ]*([0-9]*).?.?32m 元';
    
    if ( !telnet_expect_match($query_str, $match) )
    {
	echo "您無法使用匯款功能！\n";
	exit(0);
    }
    $gold_have = intval($match[1]);
    unset($match);
    
    echo "您現在有銀幣 $money_have 元\n";
    echo "您現在有金幣 $gold_have 元\n";

    usleep(10000);
    if ( $money_have > (MONEY_RESERVE+MONEY_RATE) )
    {
	$money_conv = ($money_have-MONEY_RESERVE);
	if ( $money_conv > MONEY_MAX_CONV) //超過匯兌上限
	{
	    $money_conv = MONEY_MAX_CONV;
	}
	telnet_write($conn_sock, "2\n"); //匯兌
	usleep(10000);
	telnet_write($conn_sock, "1\n"); //銀幣->金幣
	usleep(10000);
	telnet_write($conn_sock, $money_conv."\n");
	//銀幣->金幣
	echo "轉換銀幣 $money_conv 元\n";
	usleep(10000);
	telnet_write($conn_sock, "y\n"); //(Y/N)
	usleep(10000);
	telnet_write($conn_sock, "\n");
	usleep(10000);
	telnet_write($conn_sock, "\n");
	usleep(10000);
	telnet_write($conn_sock, "\n");
	
	//將匯兌的銀幣也算入金幣的數量中
	$gold_have += intval($money_conv/MONEY_RATE);
    }
    
    if ( $gold_have > 0 ) //有金幣才需要存到銀行
    {
	usleep(10000);
	telnet_write($conn_sock, "3\n"); //存款
	usleep(10000);
	telnet_write($conn_sock, "2\n"); //金幣
	usleep(10000);
	telnet_write($conn_sock, $gold_have."\n"); //多少金幣
	echo "存入金幣 $gold_have 元\n";
	usleep(10000);
	telnet_write($conn_sock, "\n");
	usleep(10000);
	telnet_write($conn_sock, "\n");
	usleep(100000);
    }
    
    $is_loop = false;

}


?>
