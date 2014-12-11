<?php
$dbAddr = '127.0.0.1';
$dbUser = 'irc';
$dbPass = '*';

$prvCh = array('#private_channel_names');
$modeList = array('thm', 'full', 'xml');

$mode = $_GET['mode'];
$no = $_GET['no'];
$adult = $_GET['adult'];
$safe = $_GET['safe'];

if (array_search($mode, $modeList) === false) {
    $mode = 'full';
}

if (!is_numeric($no)){
    $no = false;
}

if ($adult != '1') $adult = 0;

$query = "SELECT * FROM image WHERE no = '{$no}'";

if ($no !== false) {
    mysql_connect($dbAddr, $dbUser, $dbPass) ? $sql = true : $sql = false;
    $sql && mysql_select_db('irc') ? $sql = true : $sql = false;
    mysql_set_charset('utf8');
    $result = mysql_query($query);
    $result ? $row = mysql_fetch_row($result) : $sql = false;
    $file = basename(parse_url($row[2], PHP_URL_PATH));
    if (strpos($file, '.') === false) $file = false;
} else {
    $sql = false;
}

if ($mode == 'xml') {
    header('Content-type: application/xml');
    if ($sql && $row[5] != '') {
        if ($row[5] >= 0) {
            if (array_search($row[6], $prvCh) !== false || empty($row[6])) {
                $channel = 'Unknown';
            } else {
                $channel = $row[6];
            }
            $byte = strlen($row[3]);
            $info = getimagesize("http://*/ircbot/viewimg.php?no={$row[0]}&mode=full");
            $date = date('Y/m/d H:i:s', $row[8]);
            
            echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<bakabot_image_data status="ok">
    <id>{$row[0]}</id>
    <code>{$row[5]}</code>
    <img>
        <url>http://*/ircbot/viewimg.php?no={$row[0]}&amp;mode=full</url>
        <type>{$row[1]}</type>
        <size>{$byte}</size>
        <height>{$info[1]}</height>
        <width>{$info[0]}</width>
        <md5>{$row[9]}</md5>
    </img>
    <channel>$channel</channel>
    <user>{$row[7]}</user>
    <original>{$row[2]}</original>
    <time>{$row[8]}</time>
    <date>{$date}</date>
</bakabot_image_data>
EOF;
        } else {
            echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<bakabot_image_data status="fail">
    <id>{$row[0]}</id>
    <code>{$row[5]}</code>
    <error>DELETED</error>
</bakabot_image_data>
EOF;
        }
    } else {
        echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<bakabot_image_data status="fail">
    <code>-99</code>
    <error>DB ERROR</error>
</bakabot_image_data>
EOF;
    }
    exit();
}

if ($safe == '1' && $row[5] == '0') {
    header("Content-type: image/png");
    echo file_get_contents('./safety.png');
    exit();
}

if ($mode == 'full' && $sql) {
    if ($adult == '1') {
        if ($row[5] >= 0) {
            header("Content-Type: {$row[1]}");
            if ($file) {
                header("Content-Disposition: filename=\"{$file}\"");
            }
            echo $row[3];
            exit();
        }
    } else {
        if ($row[5] >= 0 && $row[5] <= 9) {
            header("Content-type: {$row[1]}");
            if ($file) {
                header("Content-Disposition: filename=\"{$file}\"");
            }
            echo $row[3];
            exit();
        }
    }
}

if ($mode == 'thm' && $sql) {
    if ($adult == '1') {
        if ($row[5] >= 0) {
            header("Content-Type: {$row[1]}");
            echo $row[4];
            exit();
        }
    } else {
        if ($row[5] >= 0 && $row[5] <= 9) {
            header("Content-type: {$row[1]}");
            echo $row[4];
            exit();
        }
    }
}

if ($row[5] >= 10) {
    header("Content-type: image/png");
    echo file_get_contents('./r18.png');
    exit();
}

if ($row[5] === '-1' || $row[5] === '-2') {
    header("Content-type: image/png");
    echo file_get_contents('./deleted.png');
    exit();
}

header("Content-type: image/png");
echo file_get_contents('./error.png');
exit;

