<?php
error_reporting(-1);
$dbAddr = '127.0.0.1';
$dbUser = 'irc';
$dbPass = '*';

date_default_timezone_set('Asia/Tokyo');
mb_language("uni");
mb_internal_encoding("utf-8");
mb_http_input("auto");
mb_http_output("utf-8");
mb_language("Japanese");
    
if ($_COOKIE['linkTarget'] === '1' && $_GET['b'] !== '0') {
    $link = ' target="_blank"';
    $desc = "<a href=\"{$_SERVER['SCRIPT_NAME']}?b=0&c=0\">今後はリンクを同じウィンドウで開く(cookie削除)</a>";
} elseif ($_GET['b'] === '1') {
    $link = ' target="_blank"';
    $desc = "<a href=\"{$_SERVER['SCRIPT_NAME']}?b=1&c=1\">今後もリンクを新しいウィンドウで開く(cookieを使用)</a>";
} elseif($_GET['b'] === '0') {
    $link = null;
    $desc = "<a href=\"{$_SERVER['SCRIPT_NAME']}?b=1\">リンクは新しいウィンドウで開く</a> または <a href=\"{$_SERVER['SCRIPT_NAME']}?b=1&c=1\">常にリンクは新しいウィンドウで開く(cookieを使用)</a>";
}else {
    $link = null;
    $desc = "<a href=\"{$_SERVER['SCRIPT_NAME']}?b=1\">リンクは新しいウィンドウで開く</a> または <a href=\"{$_SERVER['SCRIPT_NAME']}?b=1&c=1\">常にリンクは新しいウィンドウで開く(cookieを使用)</a>";
}

if ($_GET['c'] === '1') {
    setcookie('linkTarget', '1', time() + 60 * 60 * 24 * 150, '/ircbot/', 'example.com');
} elseif ($_GET['c'] === '0') {
    setcookie('linkTarget', '0', time() - 3600, '/ircbot/', 'example.com');
}
    

echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ja" xml:lang="ja">
<head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type" />
    <title>bakabot database</title>
    <meta http-equive="content-style-type" content="text/css" />
</head>
<body>
<div>

<p>
<a href="./imglist.php">bakabot image database</a><br />
{$desc}
</p>

<table border="1">
<tr>
    <th>No.</th>
    <th>User</th>
    <th>Ch</th>
    <th>Date</th>
    <th>Body</th>
</tr>\n
EOF;

$connect = mysql_connect($dbAddr, $dbUser, $dbPass);

if ($connect) {
    mysql_select_db('irc');
    mysql_set_charset('UTF-8');
    $query = mysql_query("SELECT * FROM putdata ORDER BY no DESC", $connect);
    if ($query) {
        echo mysql_info($connect);
        while ($row = mysql_fetch_row($query)) {
            if ($row[1] == 1) {
                $date = date('y/m/d H:i', $row[4]);
                $body = htmlspecialchars($row[5]);
                $body = preg_replace('/(https?:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:@&=+$,%#]+)/i', "<a href=\"$1\"{$link}>$1</a>", $body);
                echo "<tr>\n";
                echo "    <td>{$row[0]}</td>\n";
                echo "    <td>{$row[2]}</td>\n";
                echo "    <td>{$row[3]}</td>\n";
                echo "    <td>{$date}</td>\n";
                echo "    <td>{$body}</td>\n";
                echo "</tr>\n";
            } elseif ($row[1] == 2) {
                echo "<tr>\n";
                echo "    <td>{$row[0]}</td>\n";
                echo "    <td colspan=\"4\">Deleted</td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "    <td>{$row[0]}</td>\n";
                echo "    <td colspan=\"4\">Unknown</td>\n";
                echo "</tr>\n";
            }
        }
    } else {
        echo "<tr>\n";
        echo "    <td colspan=\"5\">データの取得に失敗しました。</td>\n";
        echo "</tr>\n";
    }
    mysql_close();
} else {
    echo "<tr>\n";
    echo "    <td colspan=\"5\">データベースを開けませんでした。</td>\n";
    echo "</tr>\n";
}

echo <<<EOF
</table>
</div>
</body>
</html>
EOF;

