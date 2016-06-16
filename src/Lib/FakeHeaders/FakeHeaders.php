<?php

namespace Aszone\Component\SearchHacking\Lib\FakeHeaders;

class FakeHeaders
{
    public $pathBrowser;

    public $pathSystem;

    public $pathLocale;

    public function __construct()
    {
        $this->pathBrowser = __DIR__.'/resource/UserAgent/Browser.ini';
        $this->pathSystem = __DIR__.'/resource/UserAgent/System.ini';
        $this->pathLocale = __DIR__.'/resource/UserAgent/Locale.ini';
    }

    public function getUserAgent()
    {
        $browser = parse_ini_file($this->pathBrowser);
        $system = parse_ini_file($this->pathSystem);
        $Locale = parse_ini_file($this->pathLocale);

        $randBrowser = $browser[rand(0, count($browser) - 1)];
        $randSystem = $system[rand(0, count($system) - 1)];
        $randLocale = $Locale[rand(0, count($Locale) - 1)];

        $userAgent = $randBrowser.'/'.rand(1, 20).'.'.rand(0, 20).' ('.$randSystem.' '.rand(1, 7).'.'.rand(0, 9).'; '.$randLocale.';)';

        return array('User-Agent' => $userAgent);
    }
}
