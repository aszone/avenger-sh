<?php
/**
 * Created by PhpStorm.
 * User: lenon
 * Date: 03/04/16
 * Time: 16:30.
 */

namespace Aszone\Component\SearchHacking\Lib\ProxiesAvenger;

use Aszone\Component\SearchHacking\Lib\FakeHeaders\FakeHeaders;
use GuzzleHttp\Client;

class ProxyVirgin
{
    public function getVirginSiteProxies()
    {
        return parse_ini_file(__DIR__.'/resource/PersonalProxy.ini');
    }

    public function checkVirginProxiesExist()
    {
        $values = parse_ini_file(__DIR__.'/resource/PersonalProxy.ini');
        if (empty($values)) {
            return false;
        }

        return true;
    }

    public function getBodyByVirginProxies($urlOfSearch, $urlProxie, $proxy)
    {
        $header = new FakeHeaders();

        echo 'Proxy : '.$urlProxie."\n";

        $dataToPost = ['body' => ['url' => $urlOfSearch],
        ];

        $valid = true;
        while ($valid == true) {
            try {
                $client = new Client([
                    'defaults' => [
                        'headers' => ['User-Agent' => $header->getUserAgent()],
                        'proxy' => $proxy,
                        'timeout' => 60,
                    ],
                ]);
                $res = $client->post($urlProxie, $dataToPost);
                $body = $res->getBody()->getContents();

                //check if change new tor ip
                $valid = false;
            } catch (\Exception $e) {
                echo 'ERROR : '.$e->getMessage()."\n";
                if ($proxy == false) {
                    echo "This ip of virgin proxy is blocked, we are using proxy at now...\n";
                    //$this->pl= true;
                }

                return 'repeat';

                sleep(2);
            }
        }
       /* $crawler 	= new Crawler($body);
        $arrLinks 	= $crawler->filter('a');*/
        return $body;
    }
}
