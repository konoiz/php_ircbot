<?php
// ファイル/ページ情報を取得するのに使用するclass

class getdata
{
    
    // http経由で配布されるデータの情報を取得する関数
    function gethttp(&$irc, &$ircdata)
    {
        $title = null;
        
        // 文字列からURLを抽出
        preg_match('/h?ttps?:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:@&=+$,%#]+/', $ircdata->message, $url);
        $url = $url[0];
        if ($url[0] == 't') $url = 'h' . $url;  // ttpな時にhを補完
        
        // Proxy経由の接続が必要なページか確認
        if (check403($url)) {
            $url = urlencode($url);
            $url = "http://*/simpleproxy.php?url={$url}&k=*";
        }

        // FQDN別の処理がある場合そちらを優先
        // 今の所nicovideo.jpとamazonだけ
        $fqdn = parse_url($url, PHP_URL_HOST);
        if ('nicovideo.jp' == substr($fqdn, -12)) {
            $path = explode('/', parse_url($url, PHP_URL_PATH));
            if ($path[1] == 'watch' && !empty($path[2])) {
                $data = data_nicovideo($path[2]);
                if (empty($data)) {
                    $title = "存在しない動画っぽいです。それか情報取得に失敗しちゃったかも。。。";
                } else {
                    if ($data['site'] == 'live') {
                        $title = $data['title'];
                    } else {
                        if (mb_strlen($data['desc']) > 150) $data['desc'] = mb_substr($data['desc'], 0, 150) . "...";
                        $title = "{$data['title']} ({$data['length']}) {$data['desc']}";
                    }
                }
            }
        }
        
        /*
         elseif (strstr($fqdn, '.amazon.') !== false) {
            $title = data_html($url);
            if ($title === false) $title = "titleの取得に失敗しました。そもそもtitleがないのかも。。。(amazon)";
            $title = ConvertMsg($title, 'HTML-ENTITIES');
        }
         */

        // Content-Typeで処理方法を判断
        if (is_null($title)) {
            switch (data_header($url, 1)) {
                case 'image/jpeg':
                case 'image/png':
                case 'image/gif':
                case 'image/bmp':
                case 'image/tiff':
                    $imginfo = getimagesize($url);
                    $imgsize = ConvertUnit(data_header($url, 2), 2);
                    $title = "{$imginfo['mime']} Size: {$imgsize}byte, {$imginfo[0]}x{$imginfo[1]}px";
                    unset($imginfo, $imgsize);
                    break;
                
                case 'application/x-bittorrent':
                case 'application/bittorrent':
                    $data = data_torrent($url);
                    $title = "Torrent Name:{$data['name']} Size:{$data['size']}byte";
                    if (!$data) $title = "データの取得に失敗しました(BitTorrent)";
                    unset($data);
                    break;
                    
                case 'audio/mpeg':
                case 'audio/mp3':
                case 'audio/mpg':
                case 'audio/x-mpeg':
                case 'audio/x-mp3':
                case 'audio/x-mpg':
                case 'x-audio/mpeg':
                case 'x-audio/mp3':
                case 'x-audio/mpg':
                    $tag = id3_get_tag($url);
                    if (empty($tag['title'])) $tag['title'] = 'unknown';
                    if (empty($tag['artist'])) $tag['artist'] = 'unknown';
                    $title = "mp3 Title: {$tag['title']} / Artist: {$tag['artist']}";
                    unset($tag);
                    break;
                    
                case 'text/plain':
                    $txt   = data_get($url);
                    $txt   = preg_replace("/(\n|\r|\t|)/", "", $txt);
                    $txt   = mb_substr($txt, 0, 200);
                    $title = "{$txt}...";
                    unset($txt);
                    break;
                    
                case 'application/x-octet-stream':
                case 'application/octet-stream':
                    $body = data_get($url);
                    if (substr($body, 0, 7) == 'HL2DEMO') {
                        $data = data_hl2demo($url);
                        $title = "{$data['game']} DEMO: {$data['server']} / {$data['map']} by {$data['by']}";
                        if ($data === false) $title = "情報の取得に失敗しました(HL2DEMO)";
                    } elseif (substr($body, 0, 6) == 'HLDEMO') {
                        $data = data_hldemo($url);
                        $title = "{$data['game']} DEMO: {$data['map']}";
                        if ($data === false) $title = "情報の取得に失敗しました(HLDEMO)";
                    } elseif (substr($body, 0, 3) == 'd8:') {
                        $data = data_torrent($url);
                        $title = "Torrent Name:{$data['name']} Size:{$data['size']}byte";
                        if (!$data) $title = "データの取得に失敗しました(BitTorrent)";
                    } else {
                        $title = "知らないファイルだからよくわからないんです。。。(octet-stream)";
                    }
                    unset($data, $body);
                    break;
                    
                case 'text/html':
                case 'text/xhtml':
                case 'application/xhtml+xml':
                default:
                    $title = data_html($url);
                    if ($title === false) $title = "titleの取得に失敗しました。そもそもtitleがないのかも。。。";
                    break;
            }
        }
        
        $title = ConvertMsg($title);
        if (strpos($title, '&#')) $title = ConvertMsg($title, 'HTML-ENTITIES'); // マルチバイト文字が実体/数値参照になってるページへの対応
        $irc->message(SMARTIRC_TYPE_NOTICE, $ircdata->channel, $title);
        
        // ついでに画像をDBに流し込む
        // ここではURLを無条件にputimg.phpに送って、細かい処理はあっちでやる。
        // execを使ってbackground(-b)でwgetを起動するので、返り値は見ない。
        // できればbakabotの方でlogにerrorを出したい所だけど…
        $getimg = "http://*/ircbot/putimg.php?url=". urlencode($url) . "&ch=" . urlencode($ircdata->channel) . "&user=" . urlencode($ircdata->nick);
        exec("wget -nv -t2 -b -o /dev/null -O /dev/null \"{$getimg}\"");
        unset($getimg, $title);
    }
    
    
}










