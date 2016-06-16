<?php
/**
 * Created by PhpStorm.
 * User: lenon
 * Date: 23/04/16
 * Time: 01:43.
 */

namespace Aszone\Component\SearchHacking\Lib\Ghdb\Engineers;

use Aszone\Component\SearchHacking\Lib\Ghdb\Utils;
use Aszone\Component\SearchHacking\Lib\ProxiesAvenger\Proxies;

class DukeDukeGo
{
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

    public function run()
    {
        $exit = false;
        $count = 0;
        $numPaginator = 0;
        $countProxyVirgin = rand(0, count($this->listOfVirginProxies) - 1);
        $resultFinal = array();
        $countError = 0;

        $numberForUrl = $this->getNumberForUrl();

        while ($exit == false) {
            switch ($count) {
                case 0:
                    $numPaginator = 0;
                    break;
                case 1:
                    $numPaginator = 30;
                    break;
                case 2:
                    $numPaginator = 80;
                    break;
                case 3:
                    $numPaginator = 130;
                    break;
                case 4:
                    $numPaginator = 180;
                    break;
                default:
                    $numPaginator = 230;
                    break;

            }

            $urlOfSearch = 'https://duckduckgo.com/d.js?q='.urlencode($this->commandData['dork']).'&ct=BR&ss_mkt=us&sp=1&l=wt-wt&vqd='.$numberForUrl.'&p=1&s='.$numPaginator;
            echo 'Page '.$count."\n";

            if ($this->commandData['virginProxies']) {
                $body = $this->Proxies->getBodyByVirginProxies($urlOfSearch, $this->listOfVirginProxies[$countProxyVirgin], $this->proxy);

                $arrLinks = $this->getLinks($body);

                //Check if exist captcha
                //Check if next group of return data or not
                /*arrLinks=array();*/
                /*if(!$this->checkCaptcha($body) AND $body!="repeat"){
                    $arrLinks=$this->utils->getLinks($body);
                }else{
                    $count--;
                    echo "You has a problem with proxy, probaly you estress the engenier ...\n";
                }*/

                if ($this->checkReturnError($body)) {
                    echo "You has a problem with proxy, probaly you estress the engenier ...\n";
                    --$count;
                    ++$countError;
                    if ($countError == 4) {
                        $exit = true;
                    }
                } else {
                    $countError = 0;
                }

                //Check if next virgin proxy or repeat of 0
                if ($countProxyVirgin == count($this->listOfVirginProxies) - 1) {
                    $countProxyVirgin = 0;
                } else {
                    ++$countProxyVirgin;
                }
            } else {
                $body = $this->utils->getBody($urlOfSearch, $this->proxy);

                $arrLinks = $this->getLinks($body);
            }

            echo "\n".$urlOfSearch."\n";
            $results = $this->sanitazeLinks($arrLinks);

            if ((count($results) == 0 and $body != 'repeat')) {
                $exit = true;
            }
            $resultFinal = array_merge($resultFinal, $results);
            ++$count;
        }

        return $resultFinal;
    }

    private function getNumberForUrl()
    {
        $firstUrlOfSearch = 'https://duckduckgo.com/?q='.urlencode($this->commandData['dork']).'&search_plus_one=form&ia=web';
        $body = $this->utils->getBody($firstUrlOfSearch, $this->proxy);
        //
        $validXmlrpc = preg_match("/','.*&vqd=(.*?)&/", $body, $matches, PREG_OFFSET_CAPTURE);

        if (isset($matches[1][0])) {
            return $matches[1][0];
        }

        return false;
    }
    public function getLinks($body)
    {
        $result = [];
        $validXmlrpc = preg_match("/DDG\.Data\.languages\.resultLanguages', (.*?),{\"n\":\"/", $body, $matches, PREG_OFFSET_CAPTURE);

        if (isset($matches[1][0])) {
            $resultJson = json_decode($matches[1][0]);
            foreach ($resultJson as $resultsByLanguage) {
                foreach ($resultsByLanguage as $resultByLanguage) {
                    $result[] = $resultByLanguage;
                }
            }
        }

        return $result;
    }

    public function sanitazeLinks($links = array())
    {
        $hrefs = array();
        if (!empty($links)) {
            foreach ($links as $keyLink => $valueLink) {
                $validResultOfBlackList = $this->utils->checkBlacklist($valueLink);
                if (!$validResultOfBlackList and $valueLink) {
                    $hrefs[] = $valueLink;
                }
            }
            $hrefs = array_unique($hrefs);
        }

        return $hrefs;
    }

    public function checkReturnError($body)
    {
        $valid = preg_match("/Dvar q=window\.location\.href\.indexOf/", $body, $matches, PREG_OFFSET_CAPTURE);

        if ($valid) {
            return true;
        }

        return false;
    }
}
