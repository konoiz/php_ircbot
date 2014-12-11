<?php
$dbAddr = '127.0.0.1';
$dbUser = 'irc';
$dbPass = '66xt9d8g';

$page = $_GET['p'];
if (!is_numeric($page)) $page = 0;

// リンク関連
if ($_COOKIE['imgLinkTarget'] === '1' && $_GET['b'] !== '0') {
    $link = ' target="_blank"';
    $desc = "<a href=\"{$_SERVER['SCRIPT_NAME']}?b=0&amp;c=0{$adult}{$safe}&amp;p={$page}\">今後はリンクを同じウィンドウで開く</a>";
} elseif ($_GET['b'] === '1') {
    $link = ' target="_blank"';
    $desc = "<a href=\"{$_SERVER['SCRIPT_NAME']}?b=1&amp;c=1&amp;p={$page}\">今後もリンクを新しいウィンドウで開く(cookieを使用)</a>";
} elseif ($_GET['b'] === '0') {
    $link = null;
    $desc = "<a href=\"{$_SERVER['SCRIPT_NAME']}?b=1&amp;p={$page}\">リンクは新しいウィンドウで開く</a> または <a href=\"{$_SERVER['SCRIPT_NAME']}?b=1&amp;c=1&amp;p={$page}\">常にリンクは新しいウィンドウで開く(cookieを使用)</a>";
} else {
    $link = null;
    $desc = "<a href=\"{$_SERVER['SCRIPT_NAME']}?b=1&amp;p={$page}\">リンクは新しいウィンドウで開く</a> または <a href=\"{$_SERVER['SCRIPT_NAME']}?b=1&amp;c=1&amp;p={$page}\">常にリンクは新しいウィンドウで開く(cookieを使用)</a>";
}

// 18+関連
if ($_COOKIE['imgAdult'] === '1' && $_GET['adult'] !== '0') {
    $adult = '&amp;adult=1';
    $desc_r18 = "<a href=\"{$_SERVER['SCRIPT_NAME']}?adult=0&amp;r18c=0&amp;p={$page}\">今後は成人向け画像を表示しない</a>";
} elseif ($_GET['adult'] === '1') {
    $adult = '&amp;adult=1';
    $desc_r18 = "<a href=\"{$_SERVER['SCRIPT_NAME']}?adult=1&amp;r18c=1&amp;p={$page}\">今後も成人向け画像を表示する(cookieを使用)";
} elseif ($_GET['$adult'] === '0') {
    $adult = null;
    $desc_r18 = "<a href=\"{$_SERVER['SCRIPT_NAME']}?adult=1&amp;p={$page}\">成人向け画像を表示する</a> または <a href=\"{$_SERVER['SCRIPT_NAME']}?adult=1&amp;r18c=1&amp;p={$page}\">常に成人向け画像を表示する(cookieを使用)</a>";
} else {
    $adult = null;
    $desc_r18 = "<a href=\"{$_SERVER['SCRIPT_NAME']}?adult=1&amp;p={$page}\">成人向け画像を表示する</a> または <a href=\"{$_SERVER['SCRIPT_NAME']}?adult=1&amp;r18c=1&amp;p={$page}\">常に成人向け画像を表示する(cookieを使用)</a>";
}

// Safety関連
if ($_COOKIE['imgSafety'] === '1' && $_GET['safe'] !== '0') {
    $safe = '&amp;safe=1';
    $desc_safe = "<a href=\"{$_SERVER['SCRIPT_NAME']}?safe=0&amp;safec=0{$link}{$adult}&amp;p={$page}\">今後は中の人が未確認の画像も表示する</a>";
} elseif ($_GET['safe'] === '1') {
    $safe = '&amp;safe=1';
    $desc_safe = "<a href=\"{$_SERVER['SCRIPT_NAME']}?safe=1&amp;safec=1{$link}{$adult}&amp;p={$page}\">今後も中の人が未確認の画像は表示しない(cookieを使用)";
} elseif ($_GET['safe'] === '0') {
    $safe = null;
    $desc_safe = "<a href=\"{$_SERVER['SCRIPT_NAME']}?safe=1{$link}{$adult}&amp;p={$page}\">中の人が未確認の画像を表示しない</a> または <a href=\"{$_SERVER['SCRIPT_NAME']}?safe=1&amp;safec=1{$link}{$adult}&amp;p={$page}\">中の人が未確認の画像は常に非表示にする(cookieを使用)</a>";
} else {
    $safe = null;
    $desc_safe = "<a href=\"{$_SERVER['SCRIPT_NAME']}?safe=1{$link}{$adult}&amp;p={$page}\">中の人が未確認の画像を表示しない</a> または <a href=\"{$_SERVER['SCRIPT_NAME']}?safe=1&amp;safec=1{$link}{$adult}&amp;p={$page}\">中の人が未確認の画像は常に非表示にする(cookieを使用)</a>";
}

if ($_GET['c'] === '1') {
    setcookie('imgLinkTarget', '1', time() + 60 * 60 * 24 * 150, '/ircbot/', 'example.com');
} elseif ($_GET['c'] === '0') {
    setcookie('imgLinkTarget', '0', time() - 3600, '/ircbot/', 'example.com');
}

if ($_GET['r18c'] === '1') {
    setcookie('imgAdult', '1', time() + 60 * 60 * 24 * 150, '/ircbot/', 'example.com');
} elseif ($_GET['r18c'] === '0') {
    setcookie('imgAdult', '0', time() - 3600, '/ircbot/', 'example.com');
}

if ($_GET['safec'] === '1') {
    setcookie('imgSafety', '1', time() + 60 * 60 * 24 * 150, '/ircbot/', 'example.com');
} elseif ($_GET['safec'] === '0') {
    setcookie('imgSafety', '0', time() - 3600, '/ircbot/', 'example.com');
}

echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ja" xml:lang="ja">
<head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type" />
    <title>bakabot image database</title>
    <meta http-equiv="content-style-type" content="text/css" />
    <meta http-equiv="content-script-type" content="text/javascript" />
    
    <style type="text/css">
        img {border: 0px;}
        table, th, td {
            border: 1px #808080 solid;
            text-align: center;
            vertical-align: middle;
        }
    </style>
</head>
<body>
<div>
<p>bakabotの居るchで画像のURLを発言すると勝手に保存します。</p>
<div id="menu">
{$desc}<br />
{$desc_r18}<br />
{$desc_safe}
</div>

<script type="text/javascript">
<!--
    function showMenu(){
        document.getElementById('menu').style.display = 'block';
        document.getElementById('showMenu').style.display = 'none';
        document.getElementById('hideMenu').style.display = 'inline';
        return false;
    }
    function hideMenu(){
        document.getElementById('menu').style.display = 'none';
        document.getElementById('showMenu').style.display = 'inline';
        document.getElementById('hideMenu').style.display = 'none';
        return false;
    }
    document.getElementById('menu').style.display = "none";
    document.open();
    document.write("<a href=\"#\" id=\"showMenu\" name=\"showMenu\" style=\"display: inline\" onClick=\"showMenu()\">menuを開く</a>");
    document.write("<a href=\"#\" id=\"hideMenu\" name=\"hideMenu\" style=\"display: none\" onClick=\"hideMenu()\">menuを閉じる</a>");
    document.close();
//-->
</script>
    
EOF;

function footer() {
    echo "</div>\n";
    echo "</body>\n";
    echo "</html>\n";
}

// DBへの接続と、レコード数の確認
if (mysql_connect($dbAddr, $dbUser, $dbPass)) {
    mysql_select_db('irc');
    mysql_set_charset('utf8');
    $query = mysql_query("SELECT COUNT(*) FROM image");
    if ($query) {
        $count = mysql_result($query, 0, 0);
    } else {
        echo "データベースとの接続に問題が発生しました。";
        footer();
        exit;
    }
} else {
    echo "データベースへの接続に失敗しました。";
    footer();
    exit;
}

// ページ数の計算
if ($count % 40 == 0) {
    $pages = $count / 40;
} else {
    $pages = (($count - ($count % 40)) / 40) + 1;
}

if ($page == 0) {
    $page = $pages;
}

// 各ページへのリンク
echo "<p>Page: ";
for ($i=1;$i<=$pages;$i++) {
    if ($i == $page) {
        echo "{$i}&nbsp;";
    } else {
        echo "<a href=\"{$_SERVER['SCRIPT_NAME']}?p={$i}{$adult}{$safe}\">$i</a>&nbsp;";
    }
}
echo "</p>\n";

// 画像の表示
echo "<table>\n";
echo "    <tr>\n";

if ($page == $pages) {
    $from = ($page * 40) - 39;
    $count % 40 == 0 ? $plus = 40 : $plus = $count % 40;
    $to = ($from + ($plus)) - 1;
    $tr = true;
} else {
    $from = ($page * 40) - 39;
    $to = $page * 40;
    $tr = false;
}

for ($i=$from;$i<=$to;$i++) {
    if ( ($i%8) == 0) {
        echo "\n    <td>\n";
        echo "        <a href=\"http://*/ircbot/viewimg.php?no={$i}&amp;mode=full{$adult}{$safe}\"{$link}>\n";
        echo "            <img src=\"http://*/ircbot/viewimg.php?no={$i}&amp;mode=thm{$adult}{$safe}\" alt=\"get image no.{$i}\" />\n";
        echo "        </a>\n";
        echo "    </td>\n";
        echo "</tr>\n";
        echo "<tr>";
    } else {
        echo "\n    <td>\n";
        echo "        <a href=\"http://*/ircbot/viewimg.php?no={$i}&amp;mode=full{$adult}{$safe}\"{$link}>\n";
        echo "            <img src=\"http://*/ircbot/viewimg.php?no={$i}&amp;mode=thm{$adult}{$safe}\" alt=\"get image no{$i}\" />\n";
        echo "        </a>\n";
        echo "    </td>";
    }
}

if ($tr) {
    echo "\n</tr>";
}
echo "\n</table>\n";

// 各ページへのリンク
echo "<p>Page: ";
for ($i=1;$i<=$pages;$i++) {
    if ($i == $page) {
        echo "{$i}&nbsp;";
    } else {
        echo "<a href=\"{$_SERVER['SCRIPT_NAME']}?p={$i}\">$i</a>&nbsp;";
    }
}
echo "</p>\n";
footer();
