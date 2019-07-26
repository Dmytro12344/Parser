<?php

namespace Commands\CZ\Sluzby\asyncWithProfile;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;

class SluzbyParserCommand extends Command
{
    /**
     * Command config
     */
    protected function configure() : void
    {
        $this->setName('cz:start-3')
            ->setDescription('Starts download from http://www.biznisgroup.rs')
            ->setHelp('This command allow you start the script');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * Main parsed process (start stream)
     */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $categories = file('web/Commands/CZ/Sluzby/asyncWithProfile/list.txt', FILE_SKIP_EMPTY_LINES);
        $activeProcess = [];

        foreach($categories as $key => $category){
            try {
                for ($i = 1; $i <= 999999; $i++) {

                    $uri = $this->convertLink(trim($category), $i);
                    $process = new Process("php application.php cz:main-3 --url='$uri'");
                    $process->start();

                    $activeProcess[] = $process;

                    var_dump("$key link is processed, now $i page is processed");

                    /** Cleaning memory of useless processes */
                    $this->processControl($activeProcess);

                    /** checks last page */
                    $page = $this->pageControl($uri);

                    if(!$page && $key === count($categories) - 1){
                        sleep(60);
                    }

                    if(!$page){
                        continue 2;
                    }
                }

            } catch (ProcessFailedException $e) {
                $output->writeln([
                    $e->getMessage(),
                ]);
            }
        }
    }

    protected function pageControl(string $link) : bool
    {
        try{
            $guzzle = new GuzzleWrap();
            $crawler = new Crawler($guzzle->getContent($link));
            $nextPage = $crawler->filter('.slp-icon-chevron-right')->text();
            return true;
        }catch (\Exception $e){
            return false;
        }
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

    /**
     * @param $url
     * @return int
     * Returns total pages from category
     */
    public function getTotalPages($url) : int
    {
        $guzzle = new GuzzleWrap();
        $crawler = new Crawler($guzzle->getContent(urldecode($url)));
        try {

            $count = $crawler->filter('.nav-links > a')->count();
            return (int)$crawler->filter('.nav-links > a')->eq($count - 2)->text();

        } catch(\Exception $e){
            return 1;
        }
    }

    protected function convertLink(string $category, int $page=1) : string
    {
        return urldecode("https://katalog.sluzby.cz/$page?what=$category/1");
    }
}