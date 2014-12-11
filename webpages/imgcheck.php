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


mysql_connect($dbAddr, $dbUser, $dbPass) ? $sql = true : $sql = false;
$sql && mysql_select_db('irc') ? $sql = true : $sql = false;
mysql_set_charset('utf8');

if ($_POST['check'] == 'true' && $sql) {
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


$query = "SELECT * FROM image WHERE flag = 0";
$result = mysql_query($query);
if (!$result) $sql = false;

if ($sql) {
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

    while ($row = mysql_fetch_row($result)) {
        echo <<<EOF
        <tr>
            <td>{$row[0]}</td>
            <td>
                <a href="http://*/ircbot/viewimg.php?mode=full&no={$row[0]}" target="_blank">
                    <img src="http://*/ircbot/viewimg.php?mode=thm&no={$row[0]}" border="0" />
                </a>
            </td>
            <td>
                <input type="radio" name="res[{$row[0]}]" value="0">lator</input>
                <input type="radio" name="res[{$row[0]}]" value="1">OK</input><br />
                <input type="radio" name="res[{$row[0]}]" value="10">R18</input>
                <input type="radio" name="res[{$row[0]}]" value="-1">Del</input>
            </td>
            <td>{$row[7]}</td>
            <td>{$row[6]}</td>
            <td>{$row[2]}</td>
        </tr>
EOF;
    }
    
    echo <<<EOF
    </table>
    <input type="hidden" name="check" value="true" />
    <input type="hidden" name="keyword" value="{$_POST['keyword']}" />
    <input type="submit" value="submit" />
</form>
    
EOF;
} else {
    exit("DB ERROR.");
}
