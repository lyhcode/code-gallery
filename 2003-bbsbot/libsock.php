<?php
/* ------------------------- */
/*                           */
/* PHP-Telnet Socket Library */
/*                           */
/* ------------------------- */

/* libterm 
 * 
 * Telnet連線必須使用libterm所包含的定義
 * 必須確定libterm已經引入主程式，否則會發生錯誤
 * require_once("libterm.php");
 * 
 */


/* BBS/TELNET連線函式庫
 * 
 * 1. telnet_connect(位址, 埠號);         開啟socket連線，並傳回handle
 * 2. telnet_close(HANDLE);               關閉socket，結束連線
 * 3. telnet_write(HANDLE, 資料);         直接將資料寫入(即送出)socket
 * 4. telnet_buffer(HANDLE);              讀取(即接收)socket的資料至緩衝區
 * 5. telnet_protocol(HANDLE， 搜尋項目); 初期的TELNET協定處理，等待搜尋項目
 * 6. telnet_login(HANDLE, 帳號, 密碼, 提示字串(陣列), 事件觸發字串(陣列));
 *    提供自動化的BBS登入：
 *    提示字串[0] => 當此字串出現時，就會將帳號即密碼送出
 *    提示字串[1] => 出現這個字串，表示已經完成BBS的登入
 *    事件觸發以多維陣列組成
 *    陣列[0] {
 *        子陣列[0] = "當發現這個字串"
 *        子陣列[1] = "就送出這個字串"
 *    }
 * 7. telnet_erase();   清楚緩衝區內容
 * 8. telnet_expect();  查詢緩衝區內容，包含字串裁剪(類似Expect的功能)
 * 9. telnet_query();   查詢緩衝區內容，僅測試字串是否符合
 * 
 */


/* 如果先前並未有TIME_OUT(連線等待逾時的秒數)，就在這邊定義 */
if (!defined(TIME_OUT))
{
    define("TIME_OUT", 30);
}

/* 定義緩衝區的大小 */
define("STRING_MAX_LENGTH", 1024);

/* 緩衝區設定 */
$buff_socket = NULL;
$buff_length = NULL; //目前無作用

/* 開始使用TELNET連線 */
function telnet_connect($address, $port)
{
    if ($conn_sock = fsockopen($address, $port))
    {
	socket_set_blocking($conn_sock, 0);
	//non blocking程式才不會停滯
    }
    return( $conn_sock );
}

/* 關閉TELNET連線 */
function telnet_close($conn_sock)
{
    return( fclose($conn_sock) );
}

/* 將資料寫入SOCKET */
function telnet_write($conn_sock, $stuff)
{
    return(fwrite($conn_sock, $stuff));
}

/* 清除緩衝區資料 */
function telnet_erase()
{
    global $buff_socket;
    $buff_socket = "";
    return;
}

/* 
 * 當看到緩衝區內容包含 $expect[0]，
 * 就送出$loginid、$loginpw帳號的資料，
 * 然後以$regular的資料完成登入過程中的各種詢問訊息。
 */
function telnet_login($conn_sock, $loginid, $loginpw, $expect, $regular)
{
    global $buff_socket;  /* 需要用到緩衝區的資料 */

    //計時器
    $start_time = time();
    
    while (1)
    {
	//更新緩衝區的資料
	telnet_buffer($conn_sock);
	
	//需要送出帳號與密碼
	if ( regular_reduce($buff_socket, $expect[0]) )
	{
	    telnet_write($conn_sock, $loginid.T_CRLF); //帳號
	    telnet_write($conn_sock, $loginpw.T_CRLF); //密碼
	    continue;
	}
	
	//登入過程
	foreach ( $regular as $key => $value )
	{
	    if ( regular_reduce($buff_socket, $value[0]) )
	    {
		usleep(100000);
		telnet_buffer($conn_sock);
		telnet_buffer($conn_sock);
		telnet_erase();
		telnet_write($conn_sock, $value[1]);
	    }
	}
	
	//已經登入成功
	if ( regular_reduce($buff_socket, $expect[1]) )
	{
//	    telnet_write($conn_sock, T_BACKWARD);
//	    telnet_write($conn_sock, T_BACKWARD);
//	    telnet_write($conn_sock, T_BACKWARD);
	    return(true);
	}
	
	//連線逾時
	if ( (time() - $start_time) > TIME_OUT )
	{
	    echo "無法登入，作業逾時\n";
	    return(false);
	}
    }
}

/* 將SOCKET的資料抹寫至緩衝區 */
function telnet_buffer($conn_sock)
{
    global $buff_socket; //需要將字串存入緩衝區

    if ( !feof($conn_sock) )
    {
	$buffer = fread($conn_sock, STRING_MAX_LENGTH);
	
	if ( ($read_length = strlen($buffer)) ) //讀取長度大於零的字串
	{
	    $read_lack = STRING_MAX_LENGTH - $read_length;
	    if ( $read_lack ) //讀取之後還有剩餘空間
	    {
		//擷取舊資料來填滿緩衝區最大長度
		$buff_socket = substr($buff_socket, -$read_lack);
	    }
	    else
	    {
		//清除緩衝區內容
		$buff_socket = "";
	    }

	    $buff_socket .= $buffer;
	    
	    if (DEBUG_MODE)
	    {
		$buffer = str_replace(T_ESC, "^", $buffer);
		echo $buffer;
	    }
	}
    }
    return;
}

//查詢緩衝區的內容(包含剪裁)
function telnet_expect($string)
{
    global $buff_socket;
    return(regular_reduce($buff_socket, $string));
}

//查詢緩衝區的內容(僅測試符合)
function telnet_query($string)
{
    global $buff_socket;
    return(regular_query($buff_socket, $string));
}

//查詢緩衝區的內容(包含剪裁), 傳回MATCH
function telnet_expect_match($string, &$match)
{
    global $buff_socket;
    if ( eregi($string.'(.*)', $buff_socket, $match) )
    {
	$buff_socket = $match[(count($match)-1)];
	return(true);
    }
    return(false);
}

//查詢緩衝區的內容(僅測試符合), 傳回MATCH
function telnet_query_match($string, &$match)
{
    global $buff_socket;
    if ( eregi($string, $buff_socket, $match) )
    {
	return(true);
    }
    return(false);
}

/* 處理TELNET的協定與等待歡迎訊息 */
function telnet_protocol($conn_sock, $expect)
{
    global $buff_socket; //需要將字串存入緩衝區
    
    $time_out   = false;
    $start_time = time();

    while( !feof($conn_sock) )
    {
	if ( $byte = fgetc($conn_sock) )
	{
	    switch($byte)
	    {
		
	     case C_IAC:
		if ( ($cmd = fgetc($conn_sock)) == C_WILL )
		{
		    $code = fgetc($conn_sock);
		    if ( $code == chr(24) ) //vt100
		      fwrite($conn_sock, C_IAC.C_DO.$code);
		    else
		      fwrite($conn_sock, C_IAC.C_DONT.$code);
		}
		else if ( $cmd == C_WONT )
		  $code = fgetc($conn_sock);
		else if ( $cmd == C_DO )
		{
		    $code = fgetc($conn_sock);
		    if ( $code == chr(24) )
		      fwrite($conn_sock, C_IAC.C_WILL.$code);
		    else
		      fwrite($conn_sock, C_IAC.C_WONT.$code);
		}
		else if ( $cmd == C_DONT )
		{
		    $code = fgetc($conn_sock);
		}
		else if ( $cmd == C_SB )
		{
		    while ( ($ch = fgetc($conn_sock)) != C_SE );
		    fwrite($conn_sock,
			   C_IAC.C_SB.
			   chr(24).chr(0)."vt100".
			   C_IAC.C_SE); /* ansi */
		} //else ;
		break;
		
	     default:
		
		//if ( $byte == T_ESC ) { $byte = "^"; }
		
		$buffer_socket .= $byte;
		
		if (DEBUG_MODE)
		{
		    //將ESC(ASC/27)換成^的可見字元
		    if ( $byte == T_ESC ) { $byte = "^"; }
		    echo $byte;
		}
		break;
	    }
	}
	
	//如果已經看到歡迎訊息，就結束while迴圈
	if ( regular_query($buffer_socket, $expect) )
	{
	    if (DEBUG_MODE)
	    {
		echo "\n";
	    }
	    break;
	}
	
	//如果超過連線逾時的秒數，就結束並傳回false
	if ( (time() - $start_time) > TIME_OUT )
	{
	    echo "連線逾時\n";
	    $time_out = true;
	    break;
	    //return(false);
	}
    }
    
    //連線逾時
    if ($time_out)
    {
	return(false);
    }
    
    //表示成功
    return(true);
}
?>

