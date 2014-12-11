<?php
    
// メインの処理
class mybot
{
    // 'hi'に対して'hi'を返す
    function hello (&$irc, &$data)
    {
        global $lastHi;
        if (time() >= $lastHi + 300) {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'hi');
        }
        $lastHi = time();
    }

    // 特定の人に名前を呼ばれたら'へぇっどせっと'
    function name (&$irc, &$data)
    {
        if ($data->nick == 'xxxx') {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, mb_convert_encoding('へぇっどせっと', 'JIS', 'auto'));
        } else {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, mb_convert_encoding('いいえ', 'JIS', 'auto'));
        }
    }

    // 文章中に名前が表れたら、発言者の名前に置換して返信
    function reply (&$irc, &$data)
    {
        //mb_convert_encoding($data->message, 'UTF-8', 'auto');
        $nickname = $data->nick;
        $replyMessage = preg_replace('/BakaBot/i', $nickname, $data->message);
        $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, mb_convert_encoding($replyMessage, 'JIS', 'auto'));
        unset($replayMessage, $nickname);
    }

    // 時刻をお知らせ
    function time (&$irc)
    {
        date_default_timezone_set('Asia/Tokyo');    // タイムゾーンは東京
        global $nextTime, $timer, $stopTime, $stopTimeHour, $stopTimeMin;
        $hour = date('G');  // 時
        $min  = date('i');  // 分
        $nextTime = time() + 60;
        if ($min == 00) {
            // 分が00の時にお知らせ
            $irc->message(SMARTIRC_TYPE_CHANNEL, '#DOPE', mb_convert_encoding("{$hour}時{$min}分くらいをお知らせします", 'JIS', 'auto'));
            $irc->message(SMARTIRC_TYPE_CHANNEL, '#game_createch', mb_convert_encoding("{$hour}時{$min}分くらいをお知らせします", 'JIS', 'auto'));
        }

        if (!empty($timer) && time() >= $timer) {
            // timerがsetされていていて、現在時刻が設定時刻を上回っている場合
            $time = time();
            $errorRange = time() - $timer;
            $irc->message(SMARTIRC_TYPE_CHANNEL, '#DOPE', mb_convert_encoding("{$stopTime}分くらい経ったかも(誤差{$errorRange}秒)", 'JIS', 'auto'));
            $timer = null;  // timerをリセットする
        }
       
        if (!empty($stopTimeMin) && $stopTimeMin == $min) {
            $hour = sprintf('%02d', $hour);
            if($hour == $stopTimeHour) {
                $irc->message(SMARTIRC_TYPE_CHANNEL, '#DOPE', mb_convert_encoding("指定された時間{$stopTimeHour}時{$stopTimeMin}分になったみたいです。。。", 'JIS', 'auto'));
                $stopTimeHour = null;
                $stopTimeMin  = null;
            }
        }
    }

    function settimer (&$irc, &$data)
    {
        global $nextTime, $timer, $stopTime, $stopTimeHour, $stopTimeMin;
        if (preg_match('/^timer ([0-9]+)$/', $data->message, $settime)) {
            $stopTime = $settime[1];
            $timer = time() + ($settime[1] * 60);
            $errorRange = $nextTime - time();
            $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, mb_convert_encoding("{$stopTime}分後くらいにお知らせするかも(予想誤差{$errorRange}秒)", 'JIS', 'auto'));

        } elseif (preg_match('/^timer ([0-2]?[0-9]):([0-6]?[0-9])$/', $data->message, $settime)) {
            $stopTimeHour = sprintf('%02d', $settime[1]);
            $stopTimeMin  = sprintf('%02d', $settime[2]);
            $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, mb_convert_encoding("これから24時間以内の最初に訪れる{$stopTimeHour}時{$stopTimeMin}分くらいにお知らせするかも", 'JIS', 'auto'));
        }
    }

    function stopwatch (&$irc, &$data) {
        global $microtime;
        if (empty($microtime)) {
            $microtime = microtime(true);
            $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, mb_convert_encoding("Stopwatch Start", 'JIS', 'auto'));
        } elseif (isset($microtime)) {
            $mcountTime = round(microtime(true) - $microtime, 3);
            $mcountTime = sprintf('%014s', $mcountTime);
            $microTimeSec = (int)substr($mcountTime, 0, 9) . 0;
            $microTimeFloat = (float)substr($mcountTime, 9);
            $microTimeHis = gmdate('H:i:s', $microTimeSec);
            preg_match('/^([0-9][0-9]:[0-9][0-9]):([0-9][0-9])$/', $microTimeHis, $mOut);
            $microTimeOut = $mOut[1] . ':' . ($mOut[2] + $microTimeFloat);
            $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, mb_convert_encoding("Stop {$microTimeOut}", 'JIS', 'auto'));
            $microtime = null;
        }
    }

    // [[と]]で囲まれた文字をwikipediaのページ名として、wikipediaのurlを返す
    function wikipedia (&$irc, &$data)
    {
        $msg = mb_convert_encoding($data->message, 'UTF-8', 'auto');
        if (preg_match('/^\[\[([a-z]{2,3}):(.+?)\]\]/u', $msg, $wikiKeyword)) {
            $wikiKeyword[2] = preg_replace('/\s/u', "_", $wikiKeyword[2]);
            $wikiName = urlencode($wikiKeyword[2]);
            $url = "http://{$wikiKeyword[1]}.wikipedia.org/wiki/{$wikiName}";
            $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, $url);
        } elseif (preg_match('/\[\[(.+?)\]\]/u', $msg, $wikiKeyword)) {
            $wikiKeyword[1] = preg_replace('/\s/u', "_", $wikiKeyword[1]);
            $wikiName = urlencode($wikiKeyword[1]);
            $url = "http://ja.wikipedia.org/wiki/{$wikiName}";
            $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, $url);
        }
        unset($wikiKeyword, $wikiName, $url, $msg);
    }
    
    // !put textでデータを保存
    function textPut (&$irc, &$data)
    {
        global $dbAddr, $dbUser, $dbPass;
        // !put部分を抜いたテキストデータ
        preg_match('/^!put (.+?)$/', $data->message, $putData);
        $putData[1] = mb_convert_encoding($putData[1], 'UTF-8', 'auto');
        
        // MySQLへの登録
        // DBを開く
        if (mysql_connect($dbAddr, $dbUser, $dbPass)) {
            if (mysql_select_db('irc')) {
                mysql_set_charset('UTF-8');
                // DBクエリの生成
                $putData[1] = mysql_real_escape_string($putData[1]);
                $user       = mysql_real_escape_string($data->nick);
                $ch         = mysql_real_escape_string($data->channel);
                $date       = time();
                $query      = "INSERT INTO putdata VALUES (NULL, 1, '{$user}', '{$ch}', {$date}, '$putData[1]')";
                // クエリの実行
                if (mysql_query($query)) {
                    $insertId = mysql_insert_id();
                    if ($insertId >= 1) {
                        $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, mb_convert_encoding("No.{$insertId}として登録しました。!get {$insertId}で呼び出せます。一覧 http://*/ircbot/textdb.php", 'jis', 'auto'));
                    } else {
                        if ($insertId === false) {
                            $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, mb_convert_encoding('なんかデータの登録に失敗しちゃった…(4)', 'jis', 'auto'));
                        } else {
                            $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, mb_convert_encoding('なんかデータの登録に失敗しちゃった…(5)', 'jis', 'auto'));
                        }
                    }
                } else {
                    $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, mb_convert_encoding('なんかデータの登録に失敗しちゃった…(3)', 'jis', 'auto'));
                }
            } else {
                $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, mb_convert_encoding('なんかデータの登録に失敗しちゃった…(2)', 'jis', 'auto'));
            }
            mysql_close();
        } else {
            $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, mb_convert_encoding('なんかデータの登録に失敗しちゃった… (1)', 'jis', 'auto'));
        }
        unset($putData, $user, $ch, $data, $query, $insertId);
    }
    
    // !get Noでデータを取得
    function textGet (&$irc, &$data)
    {
        global $dbAddr, $dbUser, $dbPass;
        preg_match('/^!get ([0-9]+?)$/', $data->message, $getNo);
        
        // DBクエリの生成
        $query = "SELECT * FROM putdata WHERE no = '$getNo[1]'";
        
        // MySQLから取得
        // DBを開く
        if (mysql_connect($dbAddr, $dbUser, $dbPass)) {
            if (mysql_select_db('irc')) {
                mysql_set_charset('utf8');
                $result = mysql_query($query);
                if ($result) {
                    $row = mysql_fetch_row($result);
                    if ($row !== false) {
                        if ($row[1] == 1) {
                            $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, mb_convert_encoding("No.{$row[0]}({$row[2]}): {$row[5]}", 'jis', 'UTF-8'));
                        } elseif ($row[1] == 2) {
                            $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, mb_convert_encoding('あれ、もう消されちゃったみたいです…', 'jis', 'auto'));
                        } else {
                            $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, mb_convert_encoding('よくわからないけど見せちゃだめみたいです…', 'jis', 'auto'));
                        }
                    } else {
                        $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, mb_convert_encoding('あれ、登録されてないみたいですよ？', 'jis', 'auto'));
                    }
                } else {
                    $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, mb_convert_encoding('なんかデータの取得に失敗しちゃった…(3)', 'jis', 'auto'));
                }
            } else {
                $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, mb_convert_encoding('なんかデータの取得に失敗しちゃった…(2)', 'jis', 'auto'));
            }
            mysql_close();
        } else {
            $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, mb_convert_encoding('なんかデータの取得に失敗しちゃった…(1)', 'jis', 'auto'));
        }
        unset($getNo, $query, $result, $row);
    }
    
    // !get
    function textGetN (&$irc, &$data)
    {
        $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, mb_convert_encoding('登録されているデータの一覧 http://*/ircbot/textdb.php', 'jis', 'auto'));
    }    
    
    // チャンネルオペレータ権限を付与する
    function ops (&$irc, &$data)
    {
        if (UserCheck($data->nick, $data->host)) {
            $irc->op($data->channel, $data->nick);
        } else {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, mb_convert_encoding('どちらさまですか?', 'jis', 'auto'));
        }
    }
    
    // ユーザをkick
    function kick (&$irc, &$data)
    {
        preg_match('/^kick ([a-zA-Z0-9_]+?)$/', $data->message, $kickNick);
        if (stristr($kickNick[1], 'bakabot') !== false) {
            $kickNick[1] = $data->nick;
        }
        if (UserCheck($data->nick, $data->host)) {
            $irc->kick($data->channel, array($kickNick[1]));
        } else {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, mb_convert_encoding('どちらさまですか?', 'jis', 'auto'));
        }
        unset($kickNick);
    }

    // chにjoin
    function chjoin (&$irc, &$data)
    {
        if (strpos($data->host, '.zaq.ne.jp') !== false) {
            preg_match("/^join (#.+?)$/", $data->message, $joinch);
            $irc->join($joinch[1]);
        }
        unset($joinch);
    }
    
    // bakabotに発言させる
    function say (&$irc, &$data)
    {
        if (UserCheck($data->nick, $data->host) == 'ioaia') {
            if (preg_match("/^!say (#[^ ]+?) (.+?)$/", $data->message, $sayData) == 1) {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $sayData[1], mb_convert_encoding("{$sayData[2]}", 'jis', 'auto'));
            } else {
                $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, "syntax error: say command");
            }
        }
        unset($sayData);
    }
    
    // バージョン情報
    function version (&$irc, &$data)
    {
        global $botName, $botRevision;
        $botInfo = $botName . ' ' . $botRevision . ' :: PHP v'. PHP_VERSION . ' :: PEAR/Net/SmartIRC :: LastUpdate ' . date("Y/m/d H:i:s", getlastmod());
        $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, $botInfo);
        unset($botInfo);
    }
    
    // 処理を正常終了させる
    function quit (&$irc, &$data)
    {
        // IRCから出て終了
        if (UserCheck($data->nick, $data->host) == 'ioaia') {
            $irc->quit('see you again');
        } else {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "m9");
        }
    }
}
