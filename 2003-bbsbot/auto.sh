#!/bin/bash

num=0

while [ "$num" != "200" ]; do
echo 第 $num 次啟動程式

#cd /home/kyle/Light/DevCode/Goodfick

./telnet.php -u bbbbbbbbb -p bbbbbbbb -m mod_gp   bbs.wfc.edu.tw 23

./telnet.php -u bbbbbbbbb -p bbbbbbbb -m mod_save bbs.wfc.edu.tw 23
./telnet.php -u bbbbbbbbb -p bbbbbbbb -m mod_save bbs.wfc.edu.tw 23
./telnet.php -u bbbbbbbbb -p bbbbbbbb -m mod_save bbs.wfc.edu.tw 23

num=$(($num+1))

sleep 10

done
