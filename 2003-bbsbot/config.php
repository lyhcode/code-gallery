<?php
/* --------------------------------- */
/*                                   */
/*    PHP-Telnet : Configuration     */
/*                                   */
/* --------------------------------- */

/*
 * 大部份的程式碼都可能用到此設定檔的定義
 * 請確保設定檔能優先於其他檔案的引用
 * 以避免發生錯誤，並請依照實際需求修改
 * 
 */ 


/* BBS伺服器採用的系統 */
define("BBS_TYPE_MAPLE",    1 );
define("BBS_TYPE_FIREBIRD", 2 );
/* 目前這項設定並不會有作用 */

/* BBS的預設連線設定 */
define("BBS_HOST_NAME",     "bbs.wfc.edu.tw");
define("BBS_PORT_TELNET",   23);

/* 開啟除錯模式顯示所有訊息 */
define("DEBUG_MODE",   false);
//define("DEBUG_MODE",   true);
/* 開啟時，會輸出所有接收的文字 */

/* 連線逾時的秒數，10-30之間 */
define("TIME_OUT",     10);
/* 程式中等待回應的時間限制 */

/* 程式執行時間的秒數限制 */
define("TIME_LIMIT",   18000);
/* 依照需求調整即可，0表示無限制 */

/* 以下是進入BBS的過程中會出現的字串設定 */
/* 必須依照各BBS的實際情況修改，才能運作 */

/* 連線後會出現的訊息，(以 | 管線符號隔開多個字句) */
$BBS_WELCOME = "歡迎光臨|線上人數";

/* 開始登入(送出帳號密碼)與成功登入的項目 */
$BBS_EXPECT[] = "您的帳號";       /* 出現這個項目就需要送出帳號資料 */
$BBS_EXPECT[] = "休閒聊天區";     /* 出現這個項目則表示已經完成登入 */
/* 這項設定必須依照順序 */
/* 讀取到第一筆項目，例如"您的帳號"、"請輸入代號"
 * 就會送出帳號、密碼兩資料，並開始事件觸發比對
 * 讀取到第二筆項目，如"主功能表"、"休閒聊天區"
 * 就會就表示已經成功登入，可以繼續下個階段任務
 */ 

/* 登入過程中的事件觸發和對應的字串，請小心使用 */
$BBS_REGULAR[] = array( "請按任意鍵繼續", "\n" );
$BBS_REGULAR[] = array( "其他鍵結束",     "\n" );
$BBS_REGULAR[] = array( "您有一篇文章",   "q\n");
$BBS_REGULAR[] = array( "上述記錄",       "\n" );
$BBS_REGULAR[] = array( "公告版",         "\nq".chr(27)."[D");
			
/* 請小心使用樣式比對 Regular Expresson ，以免發生錯誤 */

/* 以上適用在連線登入、進入主選單的過程 */
/* 其他功能則是由各種模組來負責完成     */


/* -------- WARNING!! ------------- */
/* 以下的設定只是預設值可以不必修改 */
/* 請直接由命令列的參數作相關設定！ */
/* -------------------------------- */

/* 模組設定 */
define("HAVE_MODULE_SET", false);
// 不要在設定檔選擇模組，使用命令列參數,libargv

/* 以下兩個模組只能二選一 */
/* 啟用金撲克遊戲模組 */
// define("HAVE_MODULE_GP",   true);
// define("HAVE_MODULE_GP",   false);
/* 啟用銀行轉帳模組 */
// define("HAVE_MODULE_TRAN", true);
// define("HAVE_MODULE_TRAN", false);
/* 啟用銀行存款模組 */
// define("HAVE_MODULE_SAVE", true);
// define("HAVE_MODULE_SAVE", false);

/* BBS連線主機設定 */
$BBS_ADDRESS = BBS_HOST_NAME;     /* BBS連線位址 */
$BBS_TYPE    = BBS_TYPE_MAPLE;       /* BBS系統名稱 */
$BBS_PORT    = BBS_PORT_TELNET;      /* BBS連線端口 */

/* 自動登入所需要的帳號和密碼 */
$BBS_LOGINID = "noconf";  /* 連線登入使用的帳號 */
$BBS_LOGINPW = "noconf";  /* 上面那位帳號的密碼 */

/* 提示符號 */
$PROMPT = "\n".chr(27)."[1;37mbbs ".chr(27)."[1;32m$".chr(27)."[m ";

?>
