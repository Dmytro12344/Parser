<?php

namespace Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;


class CreateStartCommand extends Command
{

    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('app:start-download')
             ->setDescription('Starts download')
             ->setHelp('This command allow you start the script')
             ->addOption('links', 'l', InputOption::VALUE_REQUIRED, 'total pages from pagination ');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $links = file('list.txt', FILE_SKIP_EMPTY_LINES);
        $activeProcess = [];

        foreach($links as $key => $link) {

            $total_pages = $this->checkCountPages(trim($link));
            for ($i = 1; $i <= $total_pages; $i++) {
                $url = urldecode($this->linkPars($link, $i));
                try {
                    $process = new Process("php application.php app:vacuuming -u $url");
                    $process->start();
                    $activeProcess[] = $process;



                    var_dump($process->getPid() . " now $i page is processed");

                    /** Cleaning memory of useless processes */
                    $this->processControl($activeProcess);
                } catch (ProcessFailedException $e) {

                }
            }
        }

        $output->writeln([

        ]);
    }

    /**
     * @param $processes
     * Method that cleans memory from useless processes
     */
    public function processControl($processes) : void
    {
        if(count($processes) >= 20){
            while(count($processes) >= 20){
                foreach($processes as $key => $runningProcess){
                    if(!$runningProcess->isRunning()){
                        unset($processes[$key]);
                    }
                }
                sleep(1);
            }
        }
    }

    public function linkPars($link, $torn) : string
    {
        trim($link);
        $link = substr_replace($link,"$torn/",strlen($link)-3);
        return $link;
    }

    public function checkCountPages($url)
    {
        $guzzle = new GuzzleWrap();
        $crawler = new Crawler($guzzle->getContent($url));
            $filter = $crawler->filter('#what-where-line > span')->text();
            $filter = explode(" ", $filter );
            $total_page = (int)$filter[2] / 20;
        return ceil($total_page);
    }





}