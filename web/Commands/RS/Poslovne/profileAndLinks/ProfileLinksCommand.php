<?php

namespace Commands\RS\Poslovne\profileAndLinks;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;

class ProfileLinksCommand extends Command
{
    /**
     * Command config
    */
    protected function configure() : void
    {
        $this->setName('rs:main-20')
            ->setDescription('Starts download from http://www.privredni-imenik.com')
            ->setHelp('This command allow you start the script')
            ->addOption('url', 'u', InputOption::VALUE_REQUIRED, 'needed url for parsing');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * Main parsed process (start stream)
    */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $activeProcess = [];
        $guzzle = new GuzzleWrap();
        $crawler = new Crawler($guzzle->getContent($input->getOption('url')));

        /** Creates new Process (max of processes is total records from page) */
        $totalRecords = $this->getTotalRecords($crawler);

        for($i = 0; $i < $totalRecords; $i++ ){
            $url = $this->getProfileLink($crawler, $i);
            $process = new Process("php application.php rs:vacuuming-20 --url='$url'");
            $process->start();

            /** total processes */
            $activeProcess[] = $process;
            /** Cleaning memory of useless processes */
            $this->processControl($activeProcess);
            if($i === 9){
                sleep(10);
            }
        }

    }

    /**
     * @param $processes
     * Method that cleans memory from useless processes
    */
    public function processControl(array $processes) : void
        {
         if(count($processes) >= 5){
            while(count($processes) >= 5){
                foreach($processes as $key => $runningProcess){
                    if(!$runningProcess->isRunning()){
                        unset($processes[$key]);
                    }
                }
                sleep(1);
            }
        }
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
    */
    protected function getProfileLink(Crawler $crawler, int $k) : string
    {
        $link = $crawler->filter('.mainpagec > .list > li')->eq($k)->filter('a')->attr('href');
        return urldecode('http://poslovne-strane.co.rs'. $link);
    }

    /**
     * @param Crawler $crawler
     * @return int
     * Return total records from page
    */
    protected function getTotalRecords(Crawler $crawler) : int
    {
        try{
            return (int)$crawler->filter('.mainpagec > .list > li')->count();
        } catch (\Exception $e){
            return 0;
        }
    }

}