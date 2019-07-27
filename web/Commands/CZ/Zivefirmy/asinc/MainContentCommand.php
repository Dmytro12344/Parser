<?php

namespace Commands\CZ\Zivefirmy\asinc;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;

class MainContentCommand extends Command
{
    /**
     * Command configuration
     * php application.php app:download-main-content -u string_url
     */
    protected function configure() : void
    {
        $this->setName('cz:main-11')
            ->setDescription('Starts download')
            ->setHelp('This command allow you start the script')
            ->addOption('url', 'u', InputOption::VALUE_REQUIRED);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * Starts the flow of processes that collect information
     */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $activeProcess = [];
        $guzzle = new GuzzleWrap();
        $crawler = new Crawler($guzzle->getContent($input->getOption('url')));

        /** Creates new Process (max of processes is total pages ) */
        foreach ($this->getProfile($crawler) as $url) {
            $process = new Process("php application.php cz:vacuuming-11 --url='$url'");
            $process->start();

            /** total processes */
            $activeProcess[] = $process;
            /** Cleaning memory of useless processes */
            $this->processControl($activeProcess,$crawler);
        }
    }

    /**
     * @param $process
     * @param $crawler
     * Method that cleans memory from useless processes
     */
    protected function processControl($process, $crawler) : void
    {
        if (count($process) >= 12) {
            while (count($process) >= 12) {
                foreach ($process as $key => $runningProcess) {
                    if (!$runningProcess->isRunning()) {
                        unset($process[$key]);
                    }
                }
                sleep(1);
            }
        }
    }

    /**
     * @param $crawler
     * @return array
     * Method that collect all links from current page (in current process)
     * ->attr('href')
     */
    protected function getProfile(Crawler $crawler) : array
    {
        $url = [];
        $totalRecords =  $crawler->filter('#containerIAS > .company-item')->count();

        for($k = 0; $k < $totalRecords; $k++) {
            $filter = $crawler->filter('.block')->eq($k);
            $url[] = 'https://www.zivefirmy.cz' . trim($filter->filter('.title > a')->eq(0)->attr('href'));
        }
        return $url;
    }
}