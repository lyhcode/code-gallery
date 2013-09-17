<?php
/*
 * PHP-Telnet RoBot
 * 
 *  ========================
 *   金撲克梭哈自動遊戲模組
 *  ========================
 * 
*/


/* 啟用金撲克梭哈遊戲模組 */
// define("HAVE_MODULE_GP",   true);
// 請在config設定這個項目


/* 連續押牌的次數，必須依照現有金錢考量 */
define("VICTORY_CONTINUE", 9);
define("VICTORY_STOP",     100000000);

/* 賺錢的上限 */
define("MAX_MAKE_MONEY", 500000000);


/* 模組初始化：進入遊戲畫面 */
function module_gp_init($conn_sock)
{
    //先把之前的緩衝區都清除乾淨
    telnet_erase();
    //直接進入遊戲畫面
    telnet_write($conn_sock, "x\ng\n1\n");

    //計時器
    $start_time = time();
    
    while (1)
    {
	//將BBS送來的資料寫到緩衝區
	telnet_buffer($conn_sock);

	//看到這個詢問表示已經進入遊戲
	if ( telnet_query("請問要下.?.?多少呢") )
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
function module_gp_main($conn_sock)
{
    global $is_loop;
    
    /* 狀態ready表示可以開始選牌，matched表示找到符合的牌 */
    static $ready   = false;
    static $matched = false;

    /* 連續押牌的次數 */
    static $victory = 0;
    
    /* 這是用來計算贏幾場 */
    static $rounds  = 0;
    static $wins    = 0;
    
    /* 金額偵測的功能 */
    static $money   = 0;  //即時賺到的錢 
    static $total   = 0;  //賺到的錢合計
    
    /* 判斷是否有特殊賠率 */
    static $old_money = 0;

    /* 讀取資料 */
    telnet_buffer($conn_sock);

    /* 遊戲中 */
    if ( $ready )
    {

	/* 取得賺到的共有多少 */
	$old_money = $money;
	gp_money($money);
	
	//押到同花順和四張(15、10倍)就不再押
	/*
	if ( $old_money > 0 )
	{
	    if ( (($money/($old_money))>18) && 
		 ($victory>(VICTORY_CONTINUE-1)) )
	    {
		$victory = VICTORY_CONTINUE;
	    }
	}
	*/
	/* 收手 */
	if ( $money > VICTORY_STOP )
	{
	    $victory = VICTORY_CONTINUE;
	}
	
	/* 偵測並送出對應的牌 */
	if ( (!$matched) && gp_card_match($result) )
	{
	    $perform = gp_card_perform($result[0],
				       $result[1],
				       $result[2],
				       $result[3],
				       $result[4]);
	    
	    telnet_write($conn_sock, $perform."\n");
	    $matched = true;
	}
	
	/* 遊戲過程的詢問 */
	if ( telnet_expect("請按任意鍵繼續") )
	{
	    telnet_write($conn_sock, "\n");
	}
	
	/* 有贏錢的時候，就決定是否續牌 */
	if ( telnet_expect("您要把獎金繼續") )
	{
	    if ( $victory < VICTORY_CONTINUE )  //連續押牌
	    {
		$victory ++;
		telnet_write($conn_sock, "y\n");
		$matched = false;
	    }
	    else
	    {
		telnet_write($conn_sock, "n\n");
		$victory = 0;
	    }
	}
	/* 結束遊戲狀態，建立新的牌局 */
	if ( telnet_query("請問要下.?.?多少呢") )
	{
	    $ready = false;
	}
    }
    /* 新的牌局 */
    else
    {
	if ( telnet_expect("請問要下.?.?多少呢") )
	{
	    
	    $rounds++;
	    if ( $money>0 )
	    {
		echo_write("advantage", $money);
		$wins++;
	    }
	    echo_write("rounds", $rounds);
	    echo_write("wins",   $wins);
	    
	    telnet_write($conn_sock, "20000\nd");

	    /* 計算賺到金額 */
	    $total -= (20000*2); //扣錢
	    $total += $money; //賺錢

	    /* 新的牌局必須將之前的一些紀錄清洗 */
	    $money     = 0; //歸零
	    $old_money = 0;
	    $victory   = 0;

	    
	    echo_write("money", $total);

	    /* 設定遊戲狀態 */
	    $ready = true;
	    $matched = false;
	    
	    /* 遊戲賺到過多的錢就自動結束 */
	    if ( $total > MAX_MAKE_MONEY )  //大於?億
	    {
		echo "\n\n金錢上限過載保護裝置啟動..\n";
		
		show_money();

		$is_loop = false; //離開主程式的迴圈
		
		//exit(0);
	    }
	}
    }
}

/* 取出用來分析的撲克牌資料 */
function gp_card_match(&$result)
{
    global $buff_socket;
    
    if ( ereg('╭─╭─╭─╭─╭───╮.?.?'.
	      '│(.?.?)│(.?.?)│(.?.?)│(.?.?)│(.?.?)[ ]{4}│(.*)',
	      $buff_socket, $match) )
    {
	$buff_socket = $match[6];
	
	$result[0] = $match[1];
	$result[1] = $match[2];
	$result[2] = $match[3];
	$result[3] = $match[4];
	$result[4] = $match[5];

	return(true);
    }
    return(false);
}

/* 取出得到的錢資料 */
function gp_money(&$money)
{
    global $buff_socket;
    
    if ( ereg('輸了', $buff_socket, $match) )
    {
	$money = 0;
	return(true);
    }
    elseif ( ereg('得到(.*)元', $buff_socket, $match) )
    {
	$money = intval($match[1]);
	return(true);
    }
    return(false);
}


/* 依照得到的撲克牌轉為對應送出的按鍵碼 */
function gp_card_perform($A, $B, $C, $D, $E)
{
    if ( $A==$B && $B==$C )
    {
	if ( $C==$D )
	  return(' '.T_FORWARD.' '.T_FORWARD.' '.T_FORWARD.' '.T_FORWARD.' ');
	elseif ( $D==$E )
	  return(' '.T_FORWARD.' '.T_FORWARD.' '.T_FORWARD.' '.T_FORWARD.' ');
	else
	  return(' '.T_FORWARD.' '.T_FORWARD.' ');
    }
    elseif ( $B==$C && $C==$D )
    {
	if ( $D==$E )
	  return(' '.T_FORWARD.' '.T_FORWARD.' '.T_FORWARD.' '.T_FORWARD.' ');
	else
	  return(T_FORWARD.' '.T_FORWARD.' '.T_FORWARD.' ');
    }
    elseif ( $C==$D && $D==$E )
    {
	if ( $A==$B )
	  return(' '.T_FORWARD.' '.T_FORWARD.' '.T_FORWARD.' '.T_FORWARD.' ');
	else
	  return(T_FORWARD.T_FORWARD.' '.T_FORWARD.' '.T_FORWARD.' ');
    }
    elseif ( $A==$B && $B==$C && $C==$D && $D==$E )
    {
	return(' '.T_FORWARD.' '.T_FORWARD.' '.T_FORWARD.' '.T_FORWARD.' ');
    }
    elseif ( $A==$B )    // O O X X X
    {
	if ( $C==$D )            // O O_O_O X
	  return(' '.T_FORWARD.' '.T_FORWARD.' '.T_FORWARD.' ');
	else if ( $D==$E )       // O O X_O_O
	  return(' '.T_FORWARD.' '.T_FORWARD.T_FORWARD.' '.T_FORWARD.' ');
	else                     // O O X X X
	  return(' '.T_FORWARD.' ');
    }
    elseif ( $B==$C )  // X O O X X
      return(T_FORWARD.' '.T_FORWARD.' ');
    elseif ( $C==$D )  // X X O O X
      return(T_FORWARD.T_FORWARD.' '.T_FORWARD.' ');
    elseif ( $D==$E )  // X X X O O
      return(T_FORWARD.T_FORWARD.T_FORWARD.' '.T_FORWARD.' ');
    else
      return(T_FORWARD.T_FORWARD.T_FORWARD.T_FORWARD.' ');
}



?>
