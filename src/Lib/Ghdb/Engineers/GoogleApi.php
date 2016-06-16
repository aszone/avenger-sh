<?php

namespace Aszone\Component\SearchHacking\Lib\Ghdb\Engineers;

use Aszone\Component\SearchHacking\Lib\Ghdb\Utils;
use GuzzleHttp\Client;
use Aszone\Component\SearchHacking\Lib\ProxiesAvenger\Proxies;
use Aszone\Component\SearchHacking\Lib\FakeHeaders\FakeHeaders;

class GoogleApi
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
        $chekingVirginProxies="";
        $this->commandData=$data;
        $this->utils=new Utils();


        //check if set vp and initialize method Proxyvirgin of ProxyAvenger
        if($this->commandData['virginProxies'] OR $this->commandData['proxyOfSites'] OR $this->commandData['tor'])
        {
            $this->Proxies = new Proxies();
        }

        if($this->commandData['tor'])
        {
            $this->proxy=$this->Proxies->getTor();
        }

        if($this->commandData['proxyOfSites'])
        {
            $this->proxy=$this->Proxies->getProxyOfSites();
        }

        if($this->commandData['virginProxies'])
        {
            $this->listOfVirginProxies  = $this->Proxies->getVirginSiteProxies();
            $this->usginVirginProxies   = true;
            $chekingVirginProxies=$this->Proxies->checkVirginProxiesExist();
        }

        $result=$this->utils->validation($this->commandData['virginProxies'],$chekingVirginProxies);

        if($result)
        {
            $this->error=$result;
        }
    }

    public function run()
    {
        $countProxyVirgin = rand(0,count($this->listOfVirginProxies)-1);
        $exit=false;
        $count=0;

        $urlOfSearch="http://ajax.googleapis.com/ajax/services/search/web?v=1.0&rsz=8&q=".urlencode($this->commandData['dork'])."&userip=".$this->getIp()."&filter=1&safe=off&num=100";
        //echo $urlOfSearch;

        while ($exit == false) {
            if($this->commandData['virginProxies']) {
                $arrLinks=$this->getLinksByVirginProxies($urlOfSearch,$this->listOfVirginProxies[$countProxyVirgin]);

                if($arrLinks!="repeat"){
                    $exit=true;
                }

                if($countProxyVirgin==count($this->listOfVirginProxies)-1){
                    $countProxyVirgin=0;
                }
                else{
                    $countProxyVirgin++;
                }

            }else{
                $arrLinks=$this->getJsonSearch($urlOfSearch);
                $exit=true;
            }
            //echo "\n".$urlOfSearch."\n";
        }


        $results=$this->getJsonGoogleApi($arrLinks);
        return $this->sanitazeLinksJson($results);
    }

    private function getJsonSearch($urlOfSearch){
        $client 	= new Client();
        $body 		= $client->get($urlOfSearch)->getBody()->getContents();
        $result=json_decode($body);
        return $result;
        //return $arrLinks;
    }

    private function getJsonGoogleApi($listGoogleApi=""){
        $arrayFinal=array();
        if(isset($listGoogleApi->responseData->results)){
            foreach($listGoogleApi->responseData->results as $result){
                $arrayFinal[]=$result->url;
            }
        }
        return $arrayFinal;
    }

    private function getLinksByVirginProxies($urlOfSearch,$urlProxie)
    {

        $header= new FakeHeaders();

        echo "Proxy : ".$urlProxie."\n";

        $dataToPost=['body' =>
            ['url' => $urlOfSearch ]
        ];

        $valid=true;
        while($valid==true)
        {
            try{
                $client 	= new Client([
                    'defaults' => [
                        'headers' => ['User-Agent' => $header->getUserAgent()],
                        'proxy'   => $this->proxy,
                        'timeout' => 60
                    ]
                ]);
                $body = $client->post($urlProxie,$dataToPost)->getBody()->getContents();

                $valid      =false;
                break;
            }catch(\Exception $e){
                echo "ERROR : ".$e->getMessage()."\n";
                if($this->proxy==false){
                    echo "Your ip is blocked, we are using proxy at now...\n";
                    //$this->pl= true;
                }

                return "repeat";

                sleep(2);
            }
        }

        $arrLinks=json_decode($body);
        return $arrLinks;

    }

    private function getIp(){
        return intval(rand() % 255) . "." . intval(rand() % 255) . "." . intval(rand() % 255) . "." . intval(rand() % 255);
    }

    private function sanitazeLinksJson($links)
    {
        $hrefs=array();
        foreach ($links as $keyLink => $valueLink)
        {
            $validResultOfBlackList=$this->utils->checkBlacklist($valueLink);
            if(!$validResultOfBlackList)
            {
                $hrefs[]=$valueLink;
            }
        }
        $hrefs = array_unique($hrefs);
        return $hrefs;
    }

}