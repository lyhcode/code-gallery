<?php
/*
 * PHP-Telnet RoBot
 * 
 *  ========================
 *   銀行自動轉帳功能模組
 *  ========================
 * 
*/


/* 啟用銀行自動轉帳功能模組 */
//define("HAVE_MODULE_TRAN",   true);
//在config檔設定此項目

/* 預設值：轉帳給誰？ */
define("TRANS_TO_ID", "light");
/* 預設值：轉多少錢？ */
define("TRANS_MONEY", 10000000);

/* 模組初始化：進入銀行畫面 */
function module_tran_init($conn_sock)
{
    //先把之前的緩衝區都清除乾淨
    telnet_erase();
    //直接進入遊戲畫面
    telnet_write($conn_sock, "x\nm\nb\n");

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
function module_tran_main($conn_sock)
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
    if ( !telnet_expect_match($query_str, $match) )
    {
	echo "您無法使用匯款功能！\n";
	exit(0);
    }
    $gold_have = intval($match[1]);
    unset($match);
    
    $money_have = intval($match[1]);
    echo "您現在有銀幣 $money_have 元\n";
    echo "您現在有金幣 $gold_have 元\n";
    
    $trans_to   = readline("匯款給誰(ID)：");
    while ( (intval($trans_type) != 1) || (intval($trans_type) != 2) )
    {
	$trans_type = readline("匯款種類(1:銀幣,2:金幣)：");
    }
    $money_range = (intval($trans_type)==1)?$money_have:$gold_have;
    $trans_mm   = readline("匯多少錢(10~$money_range)：");
    
    echo
      "正在匯款給 $trans_to ",
      ((intval($trans_type)==1)?"銀幣":"金幣"),
      " $trans_mm 元";
    
    if ($trans_to == "")
    {
	$trans_to = TRANS_TO_ID; //預設值：目的帳號
    }
    if ($trans_mm == "")
    {
	$trans_mm = TRANS_MONEY; //預設值：匯款數目
    }

    usleep(10000);
    /* 直接轉帳而不判斷是否有錯誤 */
    telnet_write($conn_sock, "1\n"); //轉帳
    usleep(10000);
    telnet_write($conn_sock, $trans_to."\n"); //給誰
    usleep(10000);
    telnet_write($conn_sock, $trans_type."\n"); //種類
    usleep(10000);
    telnet_write($conn_sock, $trans_mm."\n"); //金額
    usleep(10000);
    telnet_write($conn_sock, "\n");
    usleep(10000);
    telnet_write($conn_sock, "\n");
    usleep(10000);
    telnet_write($conn_sock, "\n");
    usleep(100000);
    
    echo "交易完成!\n";
    
    $is_loop = false;

}


?>
