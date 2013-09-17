<?php
/* ------------------------- */
/*                           */
/*   PHP-Telnet Ncurses UI   */
/*                           */
/* ------------------------- */

/* 定義按鍵的ASCII代碼 */
define("UI_KEY_ESC", 27);

/* 定義各項預設的顏色名稱 */
define("UI_COLOR_NORMAL", 1);  // 普通黑白配色
define("UI_COLOR_BORDER", 2);  // 視窗邊框配色
define("UI_COLOR_TITLE",  3);  // 視窗標題色彩
define("UI_COLOR_WINDOW", 4);  // 子視窗的配色
define("UI_COLOR_ROWBAR", 5);  // 主視窗橫槓色彩
define("UI_COLOR_GROUND", 6);  // 主視窗背景顏色
define("UI_COLOR_PROMPT", 7);  // 提示字句使用的色彩

/* 保存視窗的HANDLE，勿修改變數名稱 */
$ui_main_handle    = NULL;
$ui_debug_handle   = NULL;
$ui_message_handle = NULL;
$ui_max_x = NULL;
$ui_max_y = NULL;

/* 建立ncurses的session，使用其他UI功能之前必須先使用此函數 */
function ui_create()
{
    // use ncurses getmaxyx to instead this
    // ui_getmax_xy();
    $session = ncurses_init();
    
    /* 一般ncurses的設定 */
    //ncurses_keypad();
    ncurses_cbreak();
    ncurses_noecho();
    ncurses_nonl();
    
    /* color set */
    ui_color_init();
    
    return( $session );
}

/* 繪製最基本的視窗圖形，在使用create之後必須使用此函數 */
function ui_screen_init()
{
    global $ui_main_handle;
    global $ui_max_x, $ui_max_y;
    
    /* color set */
    // ui_color_init();
    // 在ui_create使用
    
    /* main window handle */
    $ui_main_handle = ncurses_newwin(0, 0, 0, 0);

    /* use ncurses getmaxyx */
    ncurses_getmaxyx(&$ui_main_handle, $ui_max_y, $ui_max_x);
    
    /* background */
    ncurses_move(0, 0);
    ncurses_color_set(UI_COLOR_ROWBAR);
    ncurses_addstr(str_repeat(" ", $ui_max_x));

    ncurses_color_set(UI_COLOR_GROUND);
    for ( $i = 1; $i < $ui_max_y-1; $i++  )
    {
	ncurses_addstr(str_repeat(" ", $ui_max_x));
    }

    ncurses_color_set(UI_COLOR_ROWBAR);
    ncurses_addstr(str_repeat(" ", $ui_max_x));

    /* title bar */
    ncurses_attron(NCURSES_A_BOLD);
    ncurses_mvaddstr(0,0,"  PHP-Telnet RoBot UI v1.0  ");
    ncurses_mvaddstr($ui_max_y-1,0,
		     " Powered by PHP   F/f: 更新畫面  Q/q: 文字模式 ");
    ncurses_attroff(NCURSES_A_BOLD);
    
    /* refresh all */
    ncurses_refresh();
    return( $ui_main_handle );
}

/* 傳回使用者的觸發按鍵 */
function ui_key_event($press)
{
    if ( $press == "q" || $press == "Q" )
    {
	ui_close();
	return(false); //結束UI介面
    }
    elseif ( $press == "f" || $press == "F" ) 
    {
	ui_refresh();
    }
    return(true);
}

/* 更新畫面 */
function ui_refresh()
{
    global $ui_message_handle;
    ncurses_refresh();
    ncurses_wrefresh($ui_message_handle);
    return( true );
}

/* 關閉UI，結M除螢幕並結束ncurses */
function ui_close()
{
    return(  /* ncurses_clear() && */ ncurses_end()  );
}

/* 回覆螢幕的內容 */
function ui_doupdate()
{
    return(  ncurses_doupdate()  );
}

/* 取得X11終端機視窗最大的行列值，請使用ncurses的內建函式代替 */
function ui_getmax_xy()
{
    global $ui_max_x;
    global $ui_max_y;
    
    $rez = `/usr/X11R6/bin/resize`;
    $rez = explode("\n",$rez);
    while ( list($key, $val) = each($rez) )
    {
	$a = explode("=",$val);
	if( trim($a[0])=="COLUMNS" ) { $ui_max_x = intval($a[1]); }
	if( trim($a[0])=="LINES"   ) { $ui_max_y = intval($a[1]); }
    }
    return;
}

/* 開啟彩色功能，並初始化各種色彩的設定 */
function ui_color_init()
{
    if ( ncurses_has_colors() )
    {
	ncurses_start_color();
	/*
	 * Simple color assignment, often all we need.
	 */
        ncurses_init_pair(UI_COLOR_NORMAL,
			  NCURSES_COLOR_WHITE,
			  NCURSES_COLOR_BLUE);
        ncurses_init_pair(UI_COLOR_BORDER,
			  NCURSES_COLOR_CYAN,
			  NCURSES_COLOR_BLUE);
	ncurses_init_pair(UI_COLOR_TITLE,
			  NCURSES_COLOR_BLUE,
			  NCURSES_COLOR_CYAN);
	ncurses_init_pair(UI_COLOR_WINDOW,
			  NCURSES_COLOR_WHITE,
			  NCURSES_COLOR_BLUE);
	ncurses_init_pair(UI_COLOR_ROWBAR,
			  NCURSES_COLOR_YELLOW,
			  NCURSES_COLOR_BLUE);
	ncurses_init_pair(UI_COLOR_GROUND,
			  NCURSES_COLOR_BLACK,
			  NCURSES_COLOR_WHITE);
	
    }
    return;
}

/* 建立訊息輸出專用視窗 */
function ui_window_message()
{
    global $ui_message_handle;
    global $ui_max_x, $ui_max_y;
    
    $ui_message_handle = ncurses_newwin($ui_max_y-6,$ui_max_x-8,3,4);
    ncurses_wcolor_set($ui_message_handle, UI_COLOR_BORDER);
    
    for ( $i = 0; $i < $ui_max_y; $i++ )
    {
	ncurses_waddstr($ui_message_handle, str_repeat(" ", $ui_max_x));
    }

    ncurses_wborder($ui_message_handle, 0,0, 0,0, 0,0, 0,0);
    
    ncurses_wcolor_set($ui_message_handle, UI_COLOR_TITLE);
    ncurses_mvwaddstr($ui_message_handle, 0,2,
		      "  BBS Client for Programmable  ");

    // 預設視窗中文字的色彩
    ncurses_wcolor_set($ui_message_handle, UI_COLOR_WINDOW);
    
    ncurses_wrefresh($ui_message_handle);
    return $ui_message_handle;
}

/* 寫入字串到訊息視窗*/
function ui_write_message($text)
{
    global $ui_max_x, $ui_max_y;
    global $ui_message_handle;
    static $n = 2; //字串輸出位置
    $max_width = $ui_max_x-12; //超過預設寬度就截斷
    ncurses_wattron($ui_message_handle, NCURSES_A_BOLD); //高亮度模式
    ncurses_mvwaddstr($ui_message_handle, $n,2,
		      (strlen($text)>$max_width?
		       substr($text,0,$max_width):
		       $text.str_repeat(" ",$max_width-strlen($text))));
    ncurses_wrefresh($ui_message_handle);
    ncurses_wattroff($ui_message_handle, NCURSES_A_BOLD);
    $n++;
    if ( $n > ($ui_max_y-9) ) { $n = 2; }
}

function ui_window_debug()
{
}

function ui_check_size()
{
    global $ui_max_x, $ui_max_y;
    
    if ( $ui_max_x < 80 || $ui_max_y < 24 )
    {
	// end ncurses
	ui_close();
	
	echo
	  "Columns: $ui_max_x \n",
	  "Lines:   $ui_max_y \n";
	
	echo
	  "This program requires a screen size of ",
	  "at least 80 columns by 24 lines\n";
    
	exit("Terminated!-_-\n");
    }
    return(true);
}

?>
