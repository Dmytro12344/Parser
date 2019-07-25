<?php


namespace Commands\CZ\Rejstrik\async;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;

class RejstrikParserCommand extends Command
{
    protected function configure()
    {
        $this->setName('cz:start-41')
            ->setDescription('Starts download')
            ->setHelp('This command allow you start the script');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $categories = file('web/Commands/CZ/Rejstrik/async/list.txt', FILE_SKIP_EMPTY_LINES);
        $activeProcess = [];

        foreach($categories as $key => $category) {
            $totalPages = $this->getTotalPages(trim($category));

            for ($i = 1; $i <= $totalPages; $i++) {
                $url = urldecode("https://rejstrik-firem.kurzy.cz/hledej-firmy/?s=$category&page=$i");

                try {
                    $process = new Process("php application.php cz:vacuuming-41 -u '$url'");
                    $process->start();
                    $activeProcess[] = $process;

                    var_dump("$key category and $i page is processed");

                    /** Cleaning memory of useless processes */
                    $this->processControl($activeProcess);
                } catch (ProcessFailedException $e) {

                }
            }
            if($key === count($categories) - 1){
                sleep(60);
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

    protected function getTotalPages(string $category) : int
    {
        try {
            $guzzle = new GuzzleWrap();
            $link = "https://rejstrik-firem.kurzy.cz/hledej-firmy/?s=$category&page=1";
            $crawler = new Crawler($guzzle->getContent($link));

            $filter = $crawler->filter('h2 + ul + .or_paginate')->text();

            $totalPages = explode('z', $filter);
            $pages = explode(' ', $totalPages[1]);

            return (int)$pages[1];
        }
        catch (\Exception $e){
            return 1;
        }
    }
}