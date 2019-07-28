<?php

namespace Commands\RS\Biznesgroup\async;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;


class BiznesgroupParserCommand extends Command
{
    /**
     * Command config
     */
    protected function configure() : void
    {
        $this->setName('rs:start-2')
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
        $links = file('web/Commands/RS/Biznesgroup/async/list.txt', FILE_SKIP_EMPTY_LINES);
        $activeProcess = [];

        foreach($links as $key => $link){
            try {

                $total_pages = $this->getTotalPages($this->convertLink($link));

                for ($i = 1; $i <= $total_pages; $i++) {
                    $uri = $this->convertLink($link, $i);

                    $process = new Process("php application.php rs:main-2 --url='$uri'");
                    $process->start();

                    $activeProcess[] = $process;

                    var_dump("$key link is processed, now $i page is processed");

                    /** Cleaning memory of useless processes */
                    $this->processControl($activeProcess);

                    if($i === $total_pages && $key === count($links) - 1){
                        sleep(60);
                    }
                }

            } catch (ProcessFailedException $e) {
                $output->writeln([
                    $e->getMessage(),
                ]);
            }
        }
    }


    /**
     * @param $processes
     * Method that cleans memory from useless processes
     */
    public function processControl($processes) : void
    {
        if(count($processes) >= 1){
            while(count($processes) >= 1){
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

    protected function convertLink(string $link, int $page=1) : string
    {
        return substr_replace($link, $page, -2) . '/';
    }
}