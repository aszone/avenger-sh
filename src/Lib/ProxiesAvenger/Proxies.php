<?php
/**
 * Created by PhpStorm.
 * User: lenon
 * Date: 03/04/16
 * Time: 16:37.
 */

namespace Aszone\Component\SearchHacking\Lib\ProxiesAvenger;

class Proxies
{
    public $virginProxies;
    public $tor;

    public function __construct()
    {
        $this->virginProxies = new ProxyVirgin();
        $this->tor = new ProxyTor();
        $this->proxiesSite = new ProxySiteList();
    }

    public function getVirginSiteProxies()
    {
        return $this->virginProxies->getVirginSiteProxies();
    }

    public function checkVirginProxiesExist()
    {
        return $this->virginProxies->checkVirginProxiesExist();
    }

    public function getTor()
    {
        return $this->tor->getTor();
    }

    public function getProxyOfSites()
    {
        return $this->proxiesSite->getOnlyOneProxy();
    }


}
