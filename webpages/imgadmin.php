<?php
if ($_POST['keyword'] != 'xxxx') {
    echo <<<EOF
<form action="{$_SERVER['SCRIPT_NAME']}" method="post">
    <input type="text" name="keyword" />
    <input type="submit" value="submit">
</form>
EOF;

exit();
}

$dbAddr = '127.0.0.1';
$dbUser = 'irc';
$dbPass = '*';

$page = $_POST['p'];
if (!is_numeric($page)) $page = 0;

mysql_connect($dbAddr, $dbUser, $dbPass) ? $sql = true : $sql = false;
$sql && mysql_select_db('irc') ? $sql = true : $sql = false;
mysql_set_charset('utf8');
if (!$sql) exit("DB ERROR " . __LINE__);

if ($_POST['check'] == 'true') {
    foreach ($_POST['res'] as $key => $value) {
        switch ($value) {
            case '1':
                $flag = true; 
                $query = "UPDATE image SET flag = '1' WHERE no = {$key} LIMIT 1";
                break;
            case '10':
                $flag = true;
                $query = "UPDATE image SET flag = '10' WHERE no = {$key} LIMIT 1";
                break;
            case '-1':
                $flag = true;
                $query = "UPDATE image SET flag = '-1' WHERE no = {$key} LIMIT 1";
                break;
            case '0':
            default:
                $flag = false;
                break;
        }
        
        if ($flag) {
            $result = mysql_query($query);
            if ($result) {
                echo "{$key}: update {$value}<br />\n";
            } else {
                echo "{$key}: DB ERROR<br />\n";
            }
        } else {
            echo "{$key}: lator<br />\n";
        }
    }
    
    exit();
}


$query = mysql_query("SELECT COUNT(*) FROM image");
if (!$query) exit("DB ERROR " . __LINE__);
$count = mysql_result($query, 0, 0);


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
echo <<<EOF
<form action="{$_SERVER['SCRIPT_NAME']}" method="post">
    <select name="p" id="p">
EOF;

for ($i=1;$i<=$pages;$i++) {
    if ($i == $page) {
        echo "        <option value=\"{$i}\" disabled=\"disabled\">{$i}</option>\n";
    } else {
        echo "        <option value=\"{$i}\">{$i}</option>\n";
    }
}

echo <<<EOF
    </select>
    <input type="hidden" name="keyword" value="{$_POST['keyword']}" />
    <input type="submit" value="page" /> 
</form>

EOF;

if ($page == $pages) {
    $from = ($page * 40) - 39;
    $to = ($from + ($count % 40)) - 1;
} else {
    $from = ($page * 40) - 39;
    $to = $page * 40;
}

echo <<<EOF
<form actoiin="{$_SERVER['SCRIPT_NAME']}" method="post">
    <table border="1">
        <tr>
            <th>no</th>
            <th>img</th>
            <th>check</th>
            <th>user</th>
            <th>ch</th>
            <th>url</th>
        </tr>   
EOF;

for ($i=$from;$i<=$to;$i++) {
    $query = "SELECT * FROM image WHERE no = {$i}";
    $result = mysql_query($query);
    $row = mysql_fetch_row($result);
    
    if ($row) {
        echo <<<EOF
        <tr>
            <td>{$row[0]}</td>
            <td>
                <a href="http://*/ircbot/viewimg.php?mode=full&no={$row[0]}" target="_blank">
                    <img src="http://*/ircbot/viewimg.php?mode=thm&no={$row[0]}" border="0" />
                </a>
            </td>
            <td>
                <input type="radio" name="res[{$row[0]}]" value="0" checked="checked">lator</input>
                <input type="radio" name="res[{$row[0]}]" value="1">OK</input><br />
                <input type="radio" name="res[{$row[0]}]" value="10">R18</input>
                <input type="radio" name="res[{$row[0]}]" value="-1">Del</input>
            </td>
            <td>{$row[7]}</td>
            <td>{$row[6]}</td>
            <td>{$row[2]}</td>
        </tr>
EOF;
    } else {
        echo <<<EOF
        <tr>
            <td><a href="http://*/ircbot/viewimg.php?mode=full&no={$i}" target="_blank">{$i}</a></td>
            <td colspan="5">DB ERROR</td>
        </tr>
EOF;
    }
}

echo <<<EOF
    </table>
    <input type="hidden" name="check" value="true" />
    <input type="hidden" name="keyword" value="{$_POST['keyword']}" />
    <input type="submit" value="submit" />
</form>
    
EOF;


