<?php

namespace Commands\RS\Poslovnikontakt\profileAndLinks;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;

class PoslovnikontaktParserCommand extends Command
{
    /**
     * Command config
    */
    protected function configure() : void
    {
        $this->setName('rs:start-28')
            ->setDescription('Starts download from http://www.privredni-imenik.com')
            ->setHelp('This command allow you start the script');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * Main parsed process (start stream)
    */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $links = file('web/Commands/RS/Poslovnikontakt/profileAndLinks/list.txt', FILE_SKIP_EMPTY_LINES);
        $activeProcess = [];
        foreach($links as $key => $link){
            try{
                for($i = 1; $i <= 5; $i++){
                    $uri = $this->convertLink(trim($link), $i);
                    var_dump($uri);
                    $process = new Process("php application.php rs:main-28 --url='$uri'");
                    $process->start();
                    $activeProcess[] = $process;
                    var_dump("$key link is processed, now $i page is processed");

                    /** Cleaning memory of useless processes */
                    $this->processControl($activeProcess);

                    if($i === 5 && $key === count($links) - 1){
                        sleep(60);
                    }
                }
            } catch (\Exception $e) {

            }
        }
    }

    /**
     * @param $processes
     * Method that cleans memory from useless processes
    */
    public function processControl(array $processes) : void
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

    /**
     * @param string $link
     * @param int $page
     * @return string
    */
    protected function convertLink(string $link, int $page=1) : string
    {
        return urldecode($link . 'page/' . $page . '/');
    }

    /**
     * @param $url
     * @return int
     * Returns total pages from category
    */
    public function getTotalPages($url) : int
    {
        try {
            $guzzle = new GuzzleWrap();
            $crawler = new Crawler($guzzle->getContent(urldecode($url)));
        } catch(\Exception $e){
            return 1;
        }
    }
}