<?php

namespace Commands\RS\Mojabaza\profileAndLinks;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;

class MojabazaParserCommand extends Command
{
    /**
     * Command config
    */
    protected function configure() : void
    {
        $this->setName('rs:main-5')
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
        $links = file('web/Commands/RS/Mojabaza/profileAndLinks/list.txt', FILE_SKIP_EMPTY_LINES);
        $activeProcess = [];
        foreach($links as $key => $link){
            try{

                for($i = 1; $i <= 9999; $i++){
                    $uri = $this->convertLink(trim($link), $i);
                    $activePage = $this->isActivePage($uri);
                    $process = new Process("php application.php rs:vacuuming-5 --url='$uri'");
                    $process->start();
                    $activeProcess[] = $process;
                    var_dump("$key link is processed, now $i page is processed");

                    /** Cleaning memory of useless processes */
                    $this->processControl($activeProcess);

                    if($key === count($links) - 1 && !$activePage){
                        sleep(60);
                    }

                    /** Check content from page */
                    if(!$activePage){
                        continue 2;
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
     * @param string $link
     * @return bool
     * Checks to not empty content from page
     */
    protected function isActivePage(string $link) : bool
    {
        try{
            $guzzle = new GuzzleWrap();
            $crawler = new Crawler($guzzle->getContent($link));
            $crawler->filter('.article-container')->text();
            return true;
        } catch (\Exception $e){
            return false;
        }
    }
}