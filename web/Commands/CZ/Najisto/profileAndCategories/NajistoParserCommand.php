<?php

namespace Commands\CZ\Najisto\profileAndCategories;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;

class NajistoParserCommand extends Command
{
    /**
     * Command config
     */
    protected function configure() : void
    {
        $this->setName('cz:start-4')
            ->setDescription('Starts download from http://www.najisto.centrum.com')
            ->setHelp('This command allow you start the script');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * Main parsed process (start stream)
    */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $categories = file('web/Commands/CZ/Najisto/profileAndCategories/list.txt', FILE_SKIP_EMPTY_LINES);
        $activeProcess = [];
        foreach($categories as $key => $category){
            try{
                for($i = 1; $i <= 999999; $i++){
                    $uri = $this->convertLink(trim($category), $i);
                    $process = new Process("php application.php cz:main-4 --url='$uri'");
                    $process->start();
                    $activeProcess[] = $process;
                    var_dump( ($key + 1) ." category is processed, now $i page is processed");

                    /** Cleaning memory of useless processes */
                    $this->processControl($activeProcess);

                    $isNextPage = $this->isNextPage($uri);

                    if($key === count($categories) - 1 && !$isNextPage){
                        sleep(60);
                    }

                    if(!$isNextPage){
                        continue 2;
                    }
                }
            } catch (\Exception $e) {
                echo "\n\n\n\t\t" . $e->getMessage() . "\n\n\n\n";
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
     * @param $link
     * @return bool
     * Checks to next page
     */
    protected function isNextPage($link) : bool
    {
        $guzzle = new GuzzleWrap();
        try{
            $crawler = new Crawler($guzzle->getContent($link));
            $crawler->filter('.nextPage > a')->text();
            return true;
        }
        catch (\Exception $e){
            return false;
        }
    }

    /**
     * @param string $keyWord
     * @param int $item
     * @return string
     */
    protected function convertLink(string $keyWord, int $item=1) : string
    {
        return urldecode('https://najisto.centrum.cz/?fp=1&p='. $item . '&what=' . $keyWord);
    }
}