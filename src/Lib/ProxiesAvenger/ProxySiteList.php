<?php

namespace Aszone\Component\SearchHacking\Lib\ProxiesAvenger;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Aszone\Component\SearchHacking\Lib\FakeHeaders\FakeHeaders;

class ProxySiteList
{
    public $pathProxy;

    public $countProxylist;

    public function __construct()
    {
        $this->pathProxy = __DIR__.'/resource/proxys.json';
        $this->countProxylist = 1;
    }

    public function getProxyOfSites()
    {
        echo "Setting Proxy...\n";
        $this->registerLisSitetFreeProxyList();

        return $this->getOnlyOneProxy();
    }

    public function getOnlyOneProxy()
    {
        $str = file_get_contents($this->pathProxy);
        $proxys = json_decode($str, true);
        $resultProxy = 'tcp://';
        $resultProxy .= $proxys[$this->countProxylist]['ip'].':'.$proxys[$this->countProxylist]['port'];
        $httpsResultProxy['http'] = $resultProxy;
        $httpsResultProxy['https'] = $resultProxy;
        ++$this->countProxylist;
        echo $resultProxy."\n";

        return $httpsResultProxy;
    }

    public function registerLisSitetFreeProxyList()
    {
        $header = new FakeHeaders();

        $listProxysIni = parse_ini_file(__DIR__.'/resource/SitesProxysFree.ini');
        echo 'Loading proxys by site '.$listProxysIni[3]."\n";
        $client = new Client();
        $body = $client->get($listProxysIni[3], array(), array(
            'headers' => ['User-Agent' => $header->getUserAgent()],
        ))->getBody()->getContents();

        $crawler = new Crawler($body);
        $count = $crawler->filterXPath('//table/tbody/tr')->count();
        $listProxys = array();
        for ($i = 1; $i <= $count; ++$i) {
            $listProxys[$i]['ip'] = $crawler->filterXPath('//table/tbody/tr['.$i.']/td[1]')->text();
            $listProxys[$i]['port'] = $crawler->filterXPath('//table/tbody/tr['.$i.']/td[2]')->text();
            $listProxys[$i]['codeCountry'] = $crawler->filterXPath('//table/tbody/tr['.$i.']/td[3]')->text();
            $listProxys[$i]['country'] = $crawler->filterXPath('//table/tbody/tr['.$i.']/td[4]')->text();
            $listProxys[$i]['anonymity'] = $crawler->filterXPath('//table/tbody/tr['.$i.']/td[5]')->text();
            $listProxys[$i]['google'] = $crawler->filterXPath('//table/tbody/tr['.$i.']/td[6]')->text();
            $listProxys[$i]['https'] = $crawler->filterXPath('//table/tbody/tr['.$i.']/td[7]')->text();
            $listProxys[$i]['lastChecked'] = $crawler->filterXPath('//table/tbody/tr['.$i.']/td[8]')->text();
        }

        return $this->createJsonListProxys($listProxys);
    }

    public function createJsonListProxys($datas)
    {
        if (file_exists($this->pathProxy)) {
            unlink($this->pathProxy);
        }
        $fp = fopen($this->pathProxy, 'w');
        fwrite($fp, json_encode($datas));
        fclose($fp);

        return file_exists($this->pathProxy);
    }
}
