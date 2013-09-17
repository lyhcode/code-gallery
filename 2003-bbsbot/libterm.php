<?php
/* --------------------------- */
/* PHP-Telnet Terminal Library */
/* --------------------------- */

/* RFC 854 - Telnet Protocol Specification */
/*
 * Definitions for the TELNET protocol.
 */
define("C_IAC",   chr(255));    /* interpret as command: */
define("C_DONT",  chr(254));    /* you are not to use option */
define("C_DO",    chr(253));    /* please, you use option */
define("C_WONT",  chr(252));    /* I won't use option */
define("C_WILL",  chr(251));    /* I will use option */
define("C_SB",    chr(250));    /* interpret as subnegotiation */
define("C_GA",    chr(249));    /* you may reverse the line */
define("C_EL",    chr(248));    /* erase the current line */
define("C_EC",    chr(247));    /* erase the current character */
define("C_AYT",   chr(246));    /* are you there */
define("C_AO",    chr(245));    /* abort output--but let prog finish */
define("C_IP",    chr(244));    /* interrupt process--permanently */
define("C_BREAK", chr(243));    /* break */
define("C_DM",    chr(242));    /* data mark--for connect. cleaning */
define("C_NOP",   chr(241));    /* nop */
define("C_SE",    chr(240));    /* end sub negotiation */
define("C_EOR",   chr(239));    /* end of record (transparent mode) */
define("C_ABORT", chr(238));    /* Abort process */
define("C_SUSP",  chr(237));    /* Suspend process */
define("C_xEOF",  chr(236));    /* End of file: EOF is already used... */
/* telnet options */
define("TELOPT_BINARY",  chr(0));    /* 8-bit data path */
define("TELOPT_ECHO",    chr(1));    /* echo */
define("TELOPT_TTYPE",   chr(24));   /* terminal type */
/* telnet special character */
define("T_ESC",      chr(27));
define("T_CRLF",     "\n");
define("T_FORWARD",  chr(27).'[C');
define("T_BACKWARD", chr(27).'[D');
?>
