<?php
error_reporting(-1);
mb_internal_encoding('UTF-8');  // 内部文字コードはUTF-8
mb_language("Japanese");    // mb_covert_encodingのautoの為のおまじない
ini_set('default_socket_timeout', '10');
require_once('Net/SmartIRC.php');   // PEAR::Net_SmartIRC
require_once('./main.func.php');     // 関数群
require_once('./main.class.php');  // botの処理内容
require_once('./data.class.php');   // 情報取得関連
    
// Botのバージョン情報
$botName     = 'BakaBot';
$botRevision = 'Rev.45v3';
date_default_timezone_set('Asia/Tokyo');

// データの保存に使用するデータベース
$dbAddr = '127.0.0.1';
$dbUser = 'irc';
$dbPass = '*';

// title取得時、IPアドレス制限で403になるサーバのFQDN
$list403fqdn = array('rainbow.sakuratan.com', 'rainbow2.sakuratan.com');
// IRCに出力する文字列のエンコード
$outenc = 'jis';
// SourceEngineのGame directoryとゲーム名の対応
$hl2game = array('hl2' => 'Half-Life 2', 'hl2mp' => 'Half-Life 2: Deathmatch', 'cstrike' => 'Counter-Strike: Source',
                 'dod' => 'Day of Defeat: Source', 'hl1mp' => 'Half-Life Deathmatch: Source', 'lostcoast' => 'Half-Life 2: Lost Coast',
                 'episodic' => 'Half-Life 2: Episode One', 'ep2' => 'Half-Life 2: Episode Two', 'tf' => 'Team Fortress 2',
                 'portal' => 'Portal', 'left4dead' => 'Left 4 Dead', 'left4dead2' => 'Left 4 Dead 2'
                );
$hlgame = array('cstrike' => 'Counter-Strike', 'tfc' => 'Team Fortress Classic', 'dod' => 'Day of Defeat',
                'czero' => 'Counter-Strike Condition Zero'
               );

$bot = new mybot();
$getdata = new getdata();
$irc = new Net_SmartIRC();

// メイン
//$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^[hH][iI]$', $bot, 'hello');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^[bB][aA][kK][aA][bB][oO][tT]$', $bot, 'name');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '.+?[bB][aA][kK][aA][bB][oO][tT].*?|.*?[bB][aA][kK][aA][bB][oO][tT].+?', $bot, 'reply');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '.*ttps?:\/\/.*', $getdata, 'gethttp');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '\[\[.+?\]\]', $bot, 'wikipedia');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^timer [0-2]?[0-9]:?[0-6]?[0-9]?$', $bot, 'settimer');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^stopwatch$', $bot, 'stopwatch');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^☆$', $bot, 'ops');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^kick [0-9a-zA-Z_]+?$', $bot, 'kick');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^join #.+?$', $bot, 'chjoin');
$irc->registerTimehandler(60000, $bot, 'time');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!put .+?$', $bot, 'textPut');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!get [0-9]+?$', $bot, 'textGet');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!get$', $bot, 'textGetN');
//$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!say', $bot, 'say');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!ver$', $bot, 'version');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^botquit$', $bot, 'quit');

$irc->connect('irc.friend-chat.jp', 6667);
$irc->login('BakaBot', 'BakaBot');
$irc->join('#ioaia');

$irc->setAutoRetry(TRUE);
$irc->setAutoReconnect(TRUE);
$irc->setChannelSyncing(TRUE);
$irc->listen();

?>
