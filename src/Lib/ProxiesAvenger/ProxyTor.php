<?php
/**
 * Created by PhpStorm.
 * User: lenon
 * Date: 03/04/16
 * Time: 16:36.
 */

namespace Aszone\Component\SearchHacking\Lib\ProxiesAvenger;

class ProxyTor
{
    public function getTor()
    {
        return [
            'http' => 'socks5://127.0.0.1:9050',
            'https' => 'socks5://127.0.0.1:9050',
        ];
    }
}
