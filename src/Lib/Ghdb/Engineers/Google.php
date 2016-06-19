<?php

namespace Aszone\Component\SearchHacking\Lib\Ghdb\Engineers;

use Aszone\Component\SearchHacking\Lib\Ghdb\Utils;
use Aszone\FakeHeaders;
use GuzzleHttp\Client;
use Aszone\ProxyAvenger;

class Google
{
    public $siteGoogle;
    public $listOfVirginProxies;
    public $usginVirginProxies;
    public $tor;
    public $commandData;
    public $proxy;
    public $error;
    public $utils;
    public $Proxies;

    public function __construct($data)
    {
        $this->commandData = $data;
        $this->utils = new Utils();

        //check if set vp and initialize method Proxyvirgin of ProxyAvenger
        if ($this->commandData['virginProxies'] or $this->commandData['proxyOfSites'] or $this->commandData['tor']) {
            $this->Proxies = new Proxies();
        }

        if ($this->commandData['tor']) {
            $this->proxy = $this->Proxies->getTor();
        }

        if ($this->commandData['proxyOfSites']) {
            $this->proxy = $this->Proxies->getProxyOfSites();
        }

        $this->getSiteGoogle();
        $checkVirginProxiesExist = '';
        if ($this->commandData['virginProxies']) {
            $this->listOfVirginProxies = $this->Proxies->getVirginSiteProxies();
            $this->usginVirginProxies = true;
            $checkVirginProxiesExist = $this->Proxies->checkVirginProxiesExist();
        }

        $result = $this->utils->validation($this->commandData['virginProxies'], $checkVirginProxiesExist);

        if ($result) {
            $this->error = $result;
        }
    }

    public function getSiteGoogle()
    {
        $ini_google_sites = parse_ini_file(__DIR__.'/../resource/AllGoogleSites.ini');
        $this->siteGoogle = $ini_google_sites[array_rand($ini_google_sites)];
    }

    public function run()
    {
        $exit = false;
        $count = 0;
        $paginator = '';
        $countProxyVirgin = rand(0, count($this->listOfVirginProxies) - 1);
        $resultFinal = array();
        $countProxyFail = array();

        while ($exit == false) {
            if ($count != 0) {
                $numPaginator = 100 * $count;
                $paginator = '&start='.$numPaginator;
            }

            $urlOfSearch = 'https://'.$this->siteGoogle.'/search?q='.urlencode($this->commandData['dork']).'&num=100&btnG=Search&pws=1'.$paginator;
            echo 'Page '.$count."\n";

            if ($this->commandData['virginProxies']) {
                $body = $this->utils->getBodyByVirginProxies($urlOfSearch, $this->listOfVirginProxies[$countProxyVirgin], $this->proxy);

                //Check if exist captcha
                //Check if next group of return data or not
                $arrLinks = array();
                if (!$this->checkCaptcha($body) and $body != 'repeat') {
                    $arrLinks = $this->utils->getLinks($body);
                } else {
                    --$count;
                    //Count the proxys with fail and all fail proxys, finish action
                    $countProxyFail[$countProxyVirgin] = $this->listOfVirginProxies[$countProxyVirgin];
                    echo "You has a problem with proxy, probaly you estress the engenier ...\n";
                }

                //Check if next virgin proxy or repeat of 0
                if ($countProxyVirgin == count($this->listOfVirginProxies) - 1) {
                    $countProxyVirgin = 0;
                } else {
                    ++$countProxyVirgin;
                }
            } else {
                $body = $this->getBody($urlOfSearch);
                $arrLinks = $this->utils->getLinks($body);
            }

            echo "\n".$urlOfSearch."\n";

            $results = $this->utils->sanitazeLinks($arrLinks);

            if (((count($results) == 0 and $body != 'repeat') and !$this->checkCaptcha($body))
                or (count($countProxyFail) == count($this->listOfVirginProxies))) {
                $exit = true;
            }
            $resultFinal = array_merge($resultFinal, $results);
            ++$count;
        }

        return $resultFinal;
    }

    public function getBody($urlOfSearch)
    {
        $header = new FakeHeaders();
        $valid = true;
//        while($valid==true)
//        {
            try {
                $client = new Client([
                    'defaults' => [
                        'headers' => ['User-Agent' => $header->getUserAgent()],
                        'proxy' => $this->proxy,
                        'timeout' => 60,
                    ],
                ]);
                $body = $client->get($urlOfSearch)->getBody()->getContents();
                //$valid=false;
                /*$crawler 	= new Crawler($body);
                $arrLinks 	= $crawler->filter('a');*/
                return $body;
            } catch (\Exception $e) {
                echo 'ERROR : '.$e->getMessage()."\n";
                if ($this->proxy == false) {
                    echo "Your ip is blocked, we are using proxy at now...\n";
                    //$valid=false;
                }
                //$this->setProxyOfSites();
                //sleep(2);
            }

        return false;
//        }
    }

    private function checkCaptcha($body)
    {
        return preg_match('/CaptchaRedirect/', $body, $matches, PREG_OFFSET_CAPTURE);
    }
}
