<?php
$dbAddr = '127.0.0.1';
$dbUser = 'irc';
$dbPass = '*';


$url  = urldecode($_GET['url']);
$channel = urldecode($_GET['ch']);
$user = urldecode($_GET['user']);
$url = str_replace(' ', '%20', $url);

// 自分の鯖内の画像は弾く
$fqdn = parse_url($url, PHP_URL_HOST);
if (gethostbyname($fqdn) == '49.212.53.140') {
    header('HTTP/1.0 400 Bad Request');
    exit("106:Rejected. ({$fqdn})");
}

// twitpic.comは/show/largeで画像がとれる
if (preg_match('#^http://twitpic\.com/([0-9a-zA-Z]+?)$#', $url, $picid)) {
    $url = 'http://twitpic.com/show/large/' . $picid[1];
}

// 元画像/サムネイルを一時保存するファイル名
$tmp = tempnam('./tmp', 'img_');
$thm = tempnam('./tmp', 'thm_');
if (!$tmp || !$thm) {
    header('HTTP/1.0 500 Internal Server Error');
    exit('100:Temporary File Create Error');
}

// 元画像をDLして$tmpに入れる
$fp = fopen($tmp,'w+');
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
//curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1) PrivateBot blog.hitobashira.org');
$data = curl_exec($ch);
fclose($fp);

if ($data === false) {
    unlink($tmp);
    header('HTTP/1.0 400 Bad Request');
    exit('101:URI Error');
}

// ファイルサイズが>16MBなファイルは弾く
if (filesize($tmp) >= 16777216) {
    unlink($tmp);
    header('HTTP/1.0 501 Not Implemented');
    exit('102:Size Over Error');
}

// mimeを教えて!
$mime = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
if (stristr($mime, 'image') === false) {
    if (stristr($mime, 'octet-stream') === false) {
        // mimeに'image'が含まれてなくてoctet-streamじゃないのは多分画像じゃない
        unlink($tmp);
        header('HTTP/1.0 400 Bad Request');
        exit('103:Not Supported File Type = ' . $mime . " Access url is {$url}");
    }
}

$info = getimagesize($tmp);
if (!$info) {
    unlink($tmp);
    header('HTTP/1.0 500 Internal Server Error');
    exit('104:Server Error');
}
$mime = $info['mime'];

// jpg/gif/png以外の画像もさようなら
switch ($info[2]) {
    case IMAGETYPE_GIF:
        $image_s = imagecreatefromgif($tmp);
        break;
    case IMAGETYPE_JPEG:
        $image_s = imagecreatefromjpeg($tmp);
        break;
    case IMAGETYPE_PNG:
        $image_s = imagecreatefrompng($tmp);
        break;
    default:
        unlink($tmp);
        header('HTTP/1.0 400 Bad Request');
        exit('105:Not Supported File type = ' . $info[4]);
}

// サムネイルの大きさを決める
// 縦横の大きい方を96pxに、そこからもう一方を計算
if ($info[0] >= $info[1]) {
    $new_width  = 96;
    $new_height = $info[1]*(96/$info[0]);
} elseif ($info[0] <= $info[1]) {
    $new_width  = $info[0]*(96/$info[1]);
    $new_height = 96; 
}

// サムネイルを作る
$image_n = imagecreatetruecolor($new_width, $new_height);
imagecopyresampled($image_n, $image_s, 0, 0, 0, 0, $new_width, $new_height,$info[0], $info[1]);
imagejpeg($image_n, $thm);
imagedestroy($image_n);

// proxyを通して画像を取得したとき用
// 元URLを取り出す
if ($fqdn == 'www5.hitobashira.org') {
    $org_query = parse_url($url, PHP_URL_QUERY);
    parse_str($org_query, $org);
    $url = $org['url'];
}

// twitpicの/show/largeなアドレスを戻す
if (!empty($picid[1])) {
    $url = 'http://twitpic.com/' . $picid[1];
}

$md5 = md5_file($tmp);

// DBに登録しませう
if (mysql_connect($dbAddr, $dbUser, $dbPass)) {
    if (mysql_select_db('irc')) {
        mysql_set_charset('UTF-8');
        
        // 既に保存されてる画像じゃないか確認
        $checkQuery = "SELECT * FROM image WHERE md5 = '{$md5}'";
        $res = mysql_query($checkQuery);
        if (mysql_num_rows($res) !== 0) {
            unlink($tmp);
            unlink($thm);
            header('HTTP/1.0 400 Bad Request');
            exit('109:Rejected (already)');
        }
        
        $fp = fopen($tmp, 'r');
        $imgData = mysql_real_escape_string(fread($fp, filesize($tmp)));
        fclose($fp);
        
        $fp = fopen($thm, 'r');
        $imgThm = mysql_real_escape_string(fread($fp, filesize($thm)));
        fclose($fp);
        
        $mime = mysql_real_escape_string($mime);
        $url  = mysql_real_escape_string($url);
        $channel = mysql_real_escape_string($channel);
        $user = mysql_real_escape_string($user);
        $date = time();
        
        $query = "INSERT INTO image VALUES (NULL, '{$mime}', '{$url}', '{$imgData}', '{$imgThm}', 0, '{$channel}', '{$user}', {$date}, '{$md5}')";
        if (mysql_query($query)) {
            $insertId = mysql_insert_id();
            echo "000:Save Successfully. ID.{$insertId}";
        } else {
            header('HTTP/1.0 500 Internal Server Error');
            echo "201:MySQL Query Error";
        }
    } else {
        header('HTTP/1.0 500 Internal Server Error');
        echo "202:MySQL Database Error";
    }
} else {
    header('HTTP/1.0 500 Internal Server Error');
    echo "203:MySQL Connection Error";
}

unlink($tmp);
unlink($thm);
exit();
