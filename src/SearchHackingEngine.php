<?php

namespace Aszone\Avenger;

use Aszone\Vulnerabilities\CrossSiteScripting;
use Aszone\Vulnerabilities\LocalFileInclusion;
use Knp\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Helper\Table;
use Aszone\SearchHacking\SearchHacking;
use Aszone\Vulnerabilities\SqlInjection;
use Aszone\Vulnerabilities\LocalFileDownload;
use Aszone\Avenger\Mailer;
use Aszone\Hacking\DefaultSite;
use Aszone\HackingAnalyzeStaticFiles\DownloadByLocalFileDownload;

class SearchHackingEngine extends Command
{
    public $tor;
    public $torl;
    public $vp;
    public $dork;
    public $email;
    public $enginers;
    public $txt;
    public $pl;
    public $check;

    public function __construct()
    {
        parent::__construct('sh');
    }

    protected function configure()
    {
        $this
              ->setDescription('Search Hacking is a tool to find detailed results in various search engines')
            ->setDefinition(
                new InputDefinition(array(
                       new InputOption(
                        'backup-files',
                        'bkp',
                        InputOption::VALUE_NONE,
                        'Set the hash. Example: --tor'),

                    new InputOption(
                        'dork',
                        'd',
                        InputOption::VALUE_REQUIRED,
                        'Set dork. Example: --dork'),

                    new InputOption(
                        'eng',
                        'e',
                        InputOption::VALUE_OPTIONAL,
                        'Set eng for exemple: --eng="google,bing". What searches enginer?',
                        array('google', 'googleapi', 'bing', 'yandex', 'dukedukego', 'yahoo', 'all')
                    ),
                    new InputOption(
                        'txt',
                        't',
                        InputOption::VALUE_REQUIRED,
                        'Set txt for name of file txt. Example: --txt="dork_for_sql"'),

                    new InputOption(
                        'check',
                        'c',
                        InputOption::VALUE_REQUIRED,
                        'Set check for verify vunerabilits. Example: --checkSqli="sqli,lfd,xss"'),

                    new InputOption(
                        'tor',
                        null,
                        InputOption::VALUE_NONE,
                        'Set tor. Example: --tor'),

                    new InputOption(
                        'torl',
                        null,
                        InputOption::VALUE_NONE,
                        'Set tor in list of results for pen test. Example: --torl'),

                    new InputOption(
                        'pl',
                        null,
                        InputOption::VALUE_NONE,
                        'Set dork. Example: --pl'),

                    new InputOption(
                        'vp',
                        null,
                        InputOption::VALUE_NONE,
                        'Set vp para virgin proxys. Example: --vp'),
                    new InputOption(
                        'email',
                        null,
                        InputOption::VALUE_NONE,
                        'Set the mail for send result. Example: --email'),
                    new InputOption(
                        'exploit',
                        null,
                        InputOption::VALUE_NONE,
                        'Set the exploit for exploit vulnerabilities, function only check lfd: --exploit'),

                    /*new InputOption(
                    	'hashs',
                    	'hss',
                    	InputOption::VALUE_REQUIRED,8/
                    	'Set the file with list of hashs. Example: --hashs=/home/foo/hashs.lst'),*/

                ))
            )
            ->setHelp('<comment>Command used to brute force</comment>');
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {


        $this->validParamns($input, $output);

        $filterProxy = array();

        $commandData = array(
            'dork' => $this->dork,
            'pl' => $this->pl,
            'tor' => $this->tor,
            'torl' => $this->torl,
            'virginProxies' => $this->vp,
            'check' => $this->check,
        );

        $sh = new SearchHacking($commandData);

        foreach ($this->eng as $enginer) {
            $output->writeln('<comment>*'.$enginer.'</comment>');
            switch ($enginer) {
                case 'google':
                    $result['google'] = $sh->runGoogle();
                    break;
                case 'googleapi':
                    $result['googleapi'] = $sh->runGoogleApi();
                    break;
                case 'bing':
                    $result['bing'] = $sh->runBing();
                    break;
                case 'yandex':
                    $result['yandex'] = $sh->runYandex();
                    break;
                case 'yahoo':
                    $result['yahoo'] = $sh->runYahoo();
                    break;
                case 'dukedukego':
                    $result['dukedukego'] = $sh->runDukeDukeGo();
                    break;
                case 'all':
                    $result['google'] = $sh->runGoogle();
                    $result['googleapi'] = $sh->runGoogleApi();
                    $result['bing'] = $sh->runBing();
                    $result['yandex'] = $sh->runYandex();
                    $result['yahoo'] = $sh->runYahoo();
                    $result['dukedukego'] = $sh->runDukeDukeGo();
                    break;
                default:
                    $output->writeln('<comment>Name Enginer not exist, help me and send email with site of searching not have you@example.com ... </comment>');
                    break;
            }

            if (isset($result[$enginer]->error)) {
                $this->printError($result, $output);
                exit();
            }
        }

        $output->writeln('');
        $output->writeln('<info>Begin Results...</info>');
        $output->writeln('');
        if (!empty($this->email)) {
            $this->sendMail($result, $this->email);
            $this->printResumeResult($output, 'Email to send:', $this->email);
        }

        //Generate name file of txt
        $nameFile = $this->createNameFile();
        if (!empty($this->txt)) {
            $nameFile = $this->txt;
        }

        //Save txt and print
        $file = $this->saveTxt($result, $nameFile);
        $this->printResult($result, $output, 'Result list of Search:');
        $this->printResumeResult($output, 'Patch File of Search:', $file);

        $this->checkVunerabilities($nameFile, $result, $commandData, $output);
    }

    protected function validParamns(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('dork')) {
            $output->writeln('<error>Please, insert your dork... </error>');
            $output->writeln('<error>example: --dork="site:com inurl:/admin"</error>');
            $this->runHelp($output);
        }

        if (!$this->sanitazeValuesOfEnginers($input->getOption('eng'))) {
            $input->setOption('eng', 'google');
            $output->writeln('<info>setting --eng="google" for default... </info>');
        }

        $this->dork = $input->getOption('dork');
        $this->vp = $input->getOption('vp');
        $this->eng = $this->sanitazeValuesOfEnginers($input->getOption('eng'));
        $this->email = $input->getOption('email');
        $this->txt = $input->getOption('txt');
        $this->tor = $input->getOption('tor');
        $this->torl = $input->getOption('torl');
        $this->check = $this->sanitazeValuesOfEnginers($input->getOption('check'));
        $this->pl = $input->getOption('pl');
        $this->exploit = $input->getOption('exploit');
    }

    private function runHelp($output)
    {
        $output->writeln('');
        $command = $this->getApplication()->find('help');
        $arguments = array(
            'command_name' => $this->getname(),
        );
        $Input = new ArrayInput($arguments);
        $returnCode = $command->run($Input, $output);
        exit();
    }

    protected function sanitazeValuesOfEnginers($enginers)
    {
        if ($enginers) {
            return @explode(',', $enginers);
        }

        return false;
    }

    protected function saveTxt($data, $filename)
    {
        $file = __DIR__.'/../results/'.$filename.'.txt';
        $myfile = fopen($file, 'w') or die('Unable to open file!');
        if (is_array($data)) {
            foreach ($data as $dataType) {
                foreach ($dataType as $singleData) {
                    $txt = $singleData."\n";
                    fwrite($myfile, $txt);
                }
            }
        } else {
            $txt = $data;
            fwrite($myfile, $txt);
        }
        fclose($myfile);

        if (!file_exists($file)) {
            return false;
        }

        return $file;
    }

    protected function sendMail($resultFinal)
    {
        //Send Mail with parcial results
        $mailer = new Mailer();
        if (empty($resultFinal)) {
            $mailer->sendMessage('you@example.com', 'Fail, not finder password in list. =\\');
        } else {
            $msg = 'PHP Avenger Informer final, list of SUCCESS:<br><br>';
            foreach ($resultFinal as $keyResultEnginer => $resultEnginer) {
                foreach ($resultEnginer as $keyResult => $result) {
                    $msg .= $keyResultEnginer.' '.$result.' <br>';
                }
            }
            $mailer->sendMessage('you@example.com', $msg);
        }
    }

    protected function createNameFile()
    {
        return $this->getName().'_'.date('m-d-Y_hia');
    }

    protected function printResult($resultFinal, OutputInterface $output, $title)
    {
        $output->writeln('');
        $output->writeln('<info>*******'.$title.'********</info>');
        $table = new Table($output);
        $table->setHeaders(array('Enginer', 'List of result'));
        $arrayToTable = array();
        foreach ($resultFinal as $keyResultEnginer => $resultEnginer) {
            foreach ($resultEnginer as $keyResult => $result) {
                $arrayToTable[] = array($keyResultEnginer, $result);
                /*$output->writeln("*<info>*".$keyResultEnginer." -> ".$result."</info>");
                $output->writeln("*-------------------------------------------------");*/
            }
        }
        $table->setRows($arrayToTable);
        $table->render();
    }

    private function printError($result, OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln('<error>'.$result['google']->error['result'].' / Command '.$result['google']->error['type'].'</error>');
    }

    protected function printResumeResult($output, $title, $info)
    {
        $output->writeln('<info>********'.$title.'********</info>');
        $output->writeln('*-------------------------------------------------');
        $output->writeln('<info>'.$info.'</info>');
        $output->writeln('*-------------------------------------------------');
        $output->writeln('');
    }

    protected function checkVunerabilities($nameFile, $result, $commandData, OutputInterface $output)
    {
        if (in_array('sqli', $this->check)) {
            $resultFinal = array();
            $nameFileSqli = $nameFile.'_sqli';
            $sqli = new SqlInjection($commandData, $result);
            $resultFinal['sqli'] = $sqli->check();
            $this->saveTxt($resultFinal, $nameFileSqli);
            $this->printResult($resultFinal, $output, 'Result list of Sqli Vulnerables:');
            $this->printResumeResult($output, 'Patch File of Sqli Vulnerables:', $nameFileSqli);
        }

        if (in_array('lfd', $this->check)) {
            $resultFinal = array();
            $nameFileLfd = $nameFile.'_lfd';
            $lfd = new LocalFileDownload($commandData, $result);
            $resultFinal['lfd'] = $lfd->check();
            $this->saveTxt($resultFinal, $nameFileLfd);
            $this->printResult($resultFinal, $output, 'Result list of Lfd Vulnerables:');
            $this->printResumeResult($output, 'Patch File of Lfd Vulnerables:', $nameFileLfd);


        }

        if (in_array('isAdmin', $this->check)) {
            $resultFinal = array();
            $nameFileIsAdmin = $nameFile.'_isAdmin';
            $site = new DefaultSite($commandData, $result);
            $resultFinal['site'] = $site->check();
            $this->saveTxt($resultFinal, $nameFileIsAdmin);
            $this->printResult($resultFinal, $output, 'Result list of admin page:');
            $this->printResumeResult($output, 'Patch File of admin page:', $nameFileIsAdmin);
        }

        if (in_array('xss', $this->check)) {

            $nameFileXss = $nameFile.'_xss';
            $site = new CrossSiteScripting($commandData, $result);
            $resultFinal['xss'] = $site->check();
            $this->saveTxt($resultFinal, $nameFileXss);
            $this->printResult($resultFinal, $output, 'Result list of Cross site Scripting:');
            $this->printResumeResult($output, 'Patch File of Cross site Scripting:', $nameFileXss);
        }

        if (in_array('lfi', $this->check)) {
            $nameFileLfi = $nameFile.'_lfi';
            $site = new LocalFileInclusion($commandData, $result);
            $resultFinal['lfi'] = $site->check();
            $this->saveTxt($resultFinal, $nameFileLfi);
            $this->printResult($resultFinal, $output, 'Result list of Local File Inclusion:');
            $this->printResumeResult($output, 'Patch File of Local File Inclusion:', $nameFileLfi);
        }

        if($this->exploit){
            $this->runExploit($resultFinal,$commandData,$output);
        }
    }

    protected function runExploit($result, $commandData, OutputInterface $output){

        if (in_array('lfd', $this->check)) {
            $output->writeln('<info>********Executing command exploit*******</info>');
            $output->writeln('<info>********Extract Files of Targets********</info>');
            $output->writeln('*-------------------------------------------------');
            $output->writeln('');
            $downloadFiles=new DownloadByLocalFileDownload($commandData);

            foreach($result['lfd'] as $url){

                $output->writeln('*-------------------------------------------------');
                $output->writeln('<info>Target => '.$url.'</info>');
                $arrDwonloadFiles=$downloadFiles->getAllFiles($url);
                $output->writeln('<info>Total of files etracted '.count($arrDwonloadFiles).' by '.$url.'</info>');
            }
            $output->writeln('*-------------------------------------------------');
            $output->writeln('find results in folder /results/lfd/');
            $output->writeln('');
        }
    }
}
