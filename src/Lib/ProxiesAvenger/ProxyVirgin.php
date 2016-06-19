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


}
