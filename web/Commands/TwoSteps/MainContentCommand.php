<?php

namespace Commands\TwoSteps;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
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
        $this->setName('app:download-main-content')
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
            $process = new Process("php application.php app:download-profile-content --url=$url");
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
        if (count($process) === count($this->getProfile($crawler))) {
            while (count($process) === count($this->getProfile($crawler))) {
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
     * @param $total
     * @param $crawler
     * @return array
     * Method that collect all links from current page (in current process)
     * ->attr('href')
     */
    protected function getProfile($crawler) : array
    {
        //$uri = 'https://www.zivefirmy.cz';
        $url = [];
        $filter = $crawler->filter('.company-card__name > a');

        for ($k = 0; $k < $filter->count(); $k++) {
            $url[] = $filter->eq($k)->attr('href');
        }
        return $url;
    }
}