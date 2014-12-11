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
    
echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ja" xml:lang="ja">
<head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type" />
    <title>bakabot database</title>
    <meta http-equive="content-style-type" content="text/css" />
</head>
</body>
<div>
<table border="1">
<tr>
    <th>No.</th>
    <th>User</th>
    <th>Ch</th>
    <th>Date</th>
    <th>Body</th>
</tr>
EOF;

$connect = mysql_connect($dbAddr, $dbUser, $dbPass);

if ($connect) {
    mysql_select_db('irc');
    mysql_set_charset('UTF-8');
    $query = mysql_query("SELECT * FROM putdata", $connect);
    if ($query) {
        echo mysql_info($connect);
        while ($row = mysql_fetch_row($query)) {
            if ($row[1] == 1) {
                $date = date('y/m/d H:i', $row[4]);
                echo "<tr>\n";
                echo "    <td>{$row[0]}</td>\n";
                echo "    <td>{$row[2]}</td>\n";
                echo "    <td>{$row[3]}</td>\n";
                echo "    <td>{$date}</td>\n";
                echo "    <td>{$row[5]}</td>\n";
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
                echo "</tr<\n";
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

