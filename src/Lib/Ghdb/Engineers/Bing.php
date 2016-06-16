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

class Bing
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
        $totalOutProxy = 5;
        $countOutProxy = 0;
        while ($exit == false) {
            if ($count != 0) {
                $numPaginator = 10 * $count;
            }

            $urlOfSearch = 'http://www.bing.com/search?q='.urlencode($this->commandData['dork']).'&filt=rf&first='.$numPaginator;
            echo 'Page '.$count."\n";

            if ($this->commandData['virginProxies']) {
                echo '*'.$countProxyVirgin.'*';
                echo '&'.$this->listOfVirginProxies[$countProxyVirgin].'&';
                $body = $this->Proxies->getBodyByVirginProxies($urlOfSearch, $this->listOfVirginProxies[$countProxyVirgin], $this->proxy);

                $arrLinks = $this->utils->getLinks($body);

                //Check if exist captcha
                //Check if next group of return data or not
                /*arrLinks=array();*/
                /*if(!$this->checkCaptcha($body) AND $body!="repeat"){
                    $arrLinks=$this->utils->getLinks($body);
                }else{
                    $count--;
                    echo "You has a problem with proxy, probaly you estress the engenier ...\n";
                }*/

                //Check if next virgin proxy or repeat of 0
                if ($countProxyVirgin == count($this->listOfVirginProxies) - 1) {
                    $countProxyVirgin = 0;
                } else {
                    ++$countProxyVirgin;
                }
            } else {
                $body = $this->utils->getBody($urlOfSearch, $this->proxy);

                $arrLinks = $this->utils->getLinks($body);

                ++$countOutProxy;
            }

            echo "\n".$urlOfSearch."\n";

            $results = $this->utils->sanitazeLinks($arrLinks);
            if ((count($results) == 0 and $body != 'repeat') or ($countOutProxy == $totalOutProxy)) {
                $exit = true;
            }
            $resultFinal = array_merge($resultFinal, $results);
            ++$count;
        }

        return $resultFinal;
    }
}
