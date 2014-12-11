<?php
// Main.classで使用する関数群を定義

// byteをKB/MB/GB/TBに変換する関数
// 第一引数に変換する数値(byte)を、第二引数に変換後の小数点以下の桁数を指定
// 引数が指定されなかった場合、いずれも0が指定されたものとし、0を返す
// http://wiki.nezweb.net/index.php?PHP%BB%C8%A4%A8%A4%EB%B4%D8%BF%F4%BD%B8
function ConvertUnit($int = 0, $digit = 0)
{
    if ($int >= pow(1024, 4)) {
        $int_t = round($int / pow(1024, 4), $digit);
        $int_t .= "T";
    } elseif ($int >= pow(1024, 3)) {
        $int_t = round($int / pow(1024, 3), $digit);
        $int_t .= "G";
    } elseif ($int >= pow(1024, 2)) {
        $int_t = round($int / pow(1024, 2), $digit);
        $int_t .= "M";
    } elseif ($int >= 1024) {
        $int_t = round($int / 1024, $digit);
        $int_t .= "K";
    } elseif ($int < 1024) {
        $int_t = round($int, $digit);
    }
    return $int_t;
}

// 登録済みユーザか確認する関数
// 第一引数に対象のユーザ名、第二引数に対象のホストを指定
// 登録されたユーザの場合はtrue、そうでない場合はfalseを返す
function UserCheck($nick, $host)
{
    $opsNick = array('xxx'         => '/^.+?\marunouchi\.tokyo\.ocn\.ne\.jp$/',
                     'yyy'       => '/^.+?\.zaq\.ne\.jp$/',
                     'zzz'         => '/^.+?\.east\.sannet\.ne\.jp$/',
    if (preg_match("/^(xxx|yyy|zzz)/", $nick, $nickname)) {
        if (preg_match($opsNick[$nickname[1]], $host)) {
            return $nickname[1];
        } else {
            return false;
        }
    } else {
        return false;
    }
}

// 登録済みのアクセス制限を受けたページか確認する関数
// 第一引数に確認するページのURLを指定
// 登録済みの場合にtrue、そうでない場合はfalseを返す
// irc.phpの$list403fqdnに対象のFQDNを設定
function check403($url)
{
    global $list403fqdn;
    $fqdn = parse_url($url, PHP_URL_HOST);
    if (array_search($fqdn, $list403fqdn)) {
        return true;
    } else {
        return false;
    }
}

// 文字を指定したコードに変換する関数
// 第一引数に変換する文字、第二引数に変換前の文字コード(=auto)
// 変換後の文字を返す
// 変換後の文字コードはirc.phpで設定。mb_convert_encodingのaliase
function ConvertMsg($msg, $inenc = null)
{
    global $outenc;
    if ($msg == '') {
        return 'Message convert error';
    } else {
        if (is_null($inenc)) $inenc = 'auto';
        return mb_convert_encoding($msg, $outenc, $inenc);
    }
}


/*********************************************************
 *    data.class.phpで使用する関数群　                      *
 *********************************************************/
// 指定されたサーバ上のファイルを取得する関数
// 第一引数にページURLを指定
// 取得出来た場合はデータの先頭32KB、失敗した場合はfalseを返す
function data_get($url)
{
    $ch = curl_init($url);
    $header = array('Connection: close');
    $option = array(CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_FOLLOWLOCATION => 1,
                    CURLOPT_RANGE => '0-5120',
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_MAXREDIRS => 5,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_HTTPHEADER => $header,
                    );
    curl_setopt_array($ch, $option);
    $body = curl_exec($ch);
    if ($body === false) {
        trigger_error(date('Y/m/d H:is')." CURL_ERR ".curl_errno($ch)." {$url}");
        return false;
    }
    return $body;
}

// 指定されたページのContent-TypeとLengthを取得する関数
// 第一引数に対象のURL、第二引数に取得する情報を指定
// 取得出来た場合にarrayかstring、失敗した場合はfalseを返す
// 適切なContent-Typeが無かった場合、NULLを返す事がある
function data_header($url, $type = 0)
{
    $ch = curl_init($url);
    $header = array('Connection: close');
    $option = array(CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_FOLLOWLOCATION => 1,
                    CURLOPT_MAXREDIRS => 5,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_NOBODY => true,
                    CURLOPT_HTTPHEADER => $header,
                    );
    curl_setopt_array($ch, $option);
    curl_exec($ch);
    if (curl_errno($ch)) {
        trigger_error(date('Y/m/d H:is')." CURL_ERR ".curl_errno($ch)." {$url}");
        return false;
    }
    $info = curl_getinfo($ch);
    //$info['content_type'] = explode(';', $info['content_type']);
    //$info['content_type'] = $info['content_type'][0];
    switch ($type) {
        case 1:
            $info = $info['content_type'];
            break;
            
        case 2:
            $info = $info['download_content_length'];
            break;
            
        case 0:
        default:
            $info = array('ctype' => $info['content_type'], 'clength' => $info['download_content_length']);
            break;
    }
    
    return $info;
}

// HTMLのtitleを取得する関数
// 第一引数にページURLを指定
// titleを取得出来た場合はtitle、失敗した場合はfalseを返す
function data_html($url)
{
    $html = data_get($url);
    if ($html === false) return false;
    $html = ConvertMsg($html);
    $html = preg_replace("/(\n|\r|\t)/", '', $html);
    if (!preg_match("/<title[^<>]*>(.*?)<\/title>/i", $html, $title)) return false;
    return $title[1];
}

// ニコニコ動画の情報を取得する関数
// 第一引数に動画IDを指定
// データを取得できた場合はarrayを、失敗した場合はfalseを返す
// live.nicovideo.jpの情報取得には未対応(代わりにget_htmlの結果を返す)
function data_nicovideo($id)
{
    if (preg_match('/^[a-z][a-z][0-9]+$/', $id) == 0) return false;
    $data = array();
    if (preg_match('/^lv[0-9]+$/', $id)) {
        // live.nicovideo.jp
        $title = data_html("http://live.nicovideo.jp/watch/{$id}");
        if ($title) {
            $data['id']    = $id;
            $data['site']  = 'live';
            $data['title'] = $title;
            return $data;
        }
    } else {
        // *.nicovideo.jp
        $nnd = simplexml_load_file("http://ext.nicovideo.jp/api/getthumbinfo/{$id}");
        if ($nnd === false) return false;
        $data['id']     = $nnd->thumb->video_id;
        if ($data['id'] == '') return false;
        $data['site']   = 'video';
        $data['title']  = $nnd->thumb->title;
        $data['length'] = $nnd->thumb->length;
        $data['desc']   = $nnd->thumb->description;
        // $data['userid'] = $nnd->thumb->user_id;
        // $data['type']   = $nnd->thumb->movie_type;
        // $data['view']   = $nnd->thumb->view_counter;
        // $data['comment']= $nnd->thumb->comment_num;
        // $data['mylist'] = $nnd->thumb->mylist_counter;
        return $data;
    }
    return false;
}

// BitTorrentファイルの情報を取得する関数
// 第一引数に対象のURLを指定
// データが取得できた場合にarray、失敗した場合にfalseを返す
function data_torrent($url)
{
    $body = data_get($url);
    if ($body === false) return false;
    $data = array();
    if (substr($body, 0, 11) != 'd8:announce') return false;
    if (!preg_match('/:name([0-9]+?):(.+?):/i', $body, $data['name'])) return false;
    $data['name'] = substr($data['name'][2], 0, $data['name'][1]);
    if (!preg_match('/:lengthi([0-9]+?)e4:/i', $body, $data['size'])) return false;
    $data['size'] = ConvertUnit($data['size'][1], 2);
    return $data;
}

// HL2(SourceEngine)のdemoファイルの情報を取得する関数
// 第一引数に対象のURLを指定
// データが取得できた場合にarray、失敗した場合にfalseを返す
function data_hl2demo($url)
{
    global $hl2game;
    $body = data_get($url);
    if ($body === false) return false;
    if (substr($body, 0, 7) != 'HL2DEMO') return false;
    $x00 = chr(0x00);
    $data = array('server' => '', 'by' => '', 'map' => '', 'dir' => '');
    for ($i=16;$i <= 266;$i++) {
        if ($body[$i] == $x00) break;
        $data['server'] .= $body[$i];
    }
    for ($i=276;$i<=526;$i++) {
        if ($body[$i] == $x00) break;
        $data['by'] .= $body[$i];
    }
    for ($i=536;$i <= 786;$i++) {
        if ($body[$i] == $x00) break;
        $data['map'] .= $body[$i];
    }
    for ($i=796;$i<=1046;$i++) {
        if ($body[$i] == $x00) break;
        $data['dir'] .= $body[$i];
    }
    $data['game'] = $data['dir'];
    if (array_key_exists($data['dir'], $hl2game)) $data['game'] = $hl2game["{$data['dir']}"];
    return $data;
}

// HL(GoldenSource)のdemoファイルの情報を取得する関数
// 第一引数に対象のURLを指定
// データが取得できた場合にarray、失敗した場合にfalseを返す
function data_hldemo($url)
{
    global $hlgame;
    $data = array('map' => '', 'dir' => '');
    $body = data_get($url);
    if ($body === false) return false;
    if (substr($body, 0, 6) != 'HLDEMO') return false;
    $x00 = chr(0x00);
    for ($i=17;$i<=266;$i++) {
        if ($body[$i] == $x00) break;
        $data['map'] .= $body[$i];
    }
    for ($i=275;$i<=526;$i++) {
        if ($body[$i] == $x00) break;
        $data['dir'] .= $body[$i];
    }
    $data['game'] = $data['dir'];
    if (array_key_exists($data['dir'], $hlgame)) $data['game'] = $hlgame["{$data['dir']}"];
    return $data;
}
