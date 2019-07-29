<?php

namespace Commands\CZ\Infoaktualne\parsByCategories;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;

class InfoaktualneParserCommand extends Command
{
    /**
     * Command config
    */
    protected function configure() : void
    {
        $this->setName('cz:start-21')
            ->setDescription('Starts download from http://www.infoaktualne.com')
            ->setHelp('This command allow you start the script');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * Main parsed process (start stream)
    */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $categories = file('web/Commands/CZ/Infoaktualne/parsByCategories/list.txt', FILE_SKIP_EMPTY_LINES);
        $activeProcess = [];
        foreach($categories as $key => $category){
            try{
                for($i = 1; $i <= 99999; $i++){
                    $uri = $this->convertLink(trim($category), $i);
                    $process = new Process("php application.php cz:vacuuming-21 --url='$uri'");
                    $process->start();
                    $activeProcess[] = $process;
                    var_dump("$key link is processed, now $i page is processed");

                    /** Cleaning memory of useless processes */
                    $this->processControl($activeProcess);
                    $activePage = $this->isActivePage($uri);
                    if($key === count($categories) - 1 && !$activePage){
                        sleep(60);
                    }

                    if(!$activePage){
                        continue 2;
                    }
                }
            } catch (Exception $e) {

            }
        }
    }

    protected function isActivePage($link) : bool
    {
        try{
            $guzzle = new GuzzleWrap();
            $crawler = new Crawler($guzzle->getContent($link));
            $crawler->filter('.item__title > a')->text();
            return true;
        } catch(Exception $e){
            return false;
        }
    }

    /**
     * @param $processes
     * Method that cleans memory from useless processes
    */
    public function processControl(array $processes) : void
        {
         if(count($processes) >= 8){
            while(count($processes) >= 8){
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
     * @param string $keyWord
     * @param int $item
     * @return string
    */
    protected function convertLink(string $keyWord, int $item=1) : string
    {
        return urldecode('http://www.infoaktualne.cz/katalog?CategoryId=-1&Filter=' . $keyWord . '&RegionId=-1&page='. $item);
    }

}