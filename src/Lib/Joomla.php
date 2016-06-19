<?php

namespace Aszone\Component\SearchHacking\Lib;

use Aszone\FakeHeaders;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Created by PhpStorm.
 * User: lenon
 * Date: 26/02/16
 * Time: 22:18.
 */
class Joomla
{
    public $target;
    public $bodyRoot;
    public $proxy;

    public function __construct($target = '', $proxy = '')
    {
        $this->target = $target;
        $this->proxy = $proxy;
        //$this->getBodyToPathRoot();
    }

    public function isJoomla()
    {
        $baseUrlJoomla = $this->target;
        $validExtension = preg_match("/^.*\.(jpg|JPG|gif|GIF|doc|DOC|pdf|PDF)$/", $this->target, $m);

        if ($validExtension) {
            $baseUrlJoomla = $this->getBaseUrlJoomla();
        }

        $header = new FakeHeaders();
        try {
            $client = new Client(['defaults' => [
                'headers' => ['User-Agent' => $header->getUserAgent()],
                'proxy' => $this->proxy,
                'timeout' => 30,
            ],
            ]);
            $body = $client->get($baseUrlJoomla)->getBody()->getContents();
            $crawler = new Crawler($body);
            $arrLinksMeta = $crawler->filter('meta');

            foreach ($arrLinksMeta as $keyLinkMeta => $valueLinkMeta) {
                $validJoomlaMeta = preg_match('/Joomla!/', $valueLinkMeta->getAttribute('content'), $m, PREG_OFFSET_CAPTURE);
                if ($validJoomlaMeta) {
                    return true;
                }
            }
            $arrLinksScript = $crawler->filter('script');
            foreach ($arrLinksScript as $keyLinkScript => $valueLinkScript) {
                $validJoomlaScript = preg_match("/(\/media\/system\/js\/mootools(.js|-core.js))/", $valueLinkScript->getAttribute('src'), $m, PREG_OFFSET_CAPTURE);
                if ($validJoomlaScript) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            return false;
        }

        return false;
    }

    public function getBodyToPathRoot()
    {
        $urlRoot = $this->getBaseUrlJoomlaByUrl();
    }

    public function getBaseUrlJoomla()
    {
        $validXmlrpc = preg_match("/^.+?[^\/:](?=[?\/]|$)/", $this->target, $m, PREG_OFFSET_CAPTURE);
        if ($validXmlrpc) {
            return $m[0][0];
        }

        return false;
    }
}
