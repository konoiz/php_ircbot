#!/bin/sh
cd /home/ioaia/ircbot
mv log.txt log.txt.bak
php irc.php  1>log.txt 2>&1 &
