<?php

namespace Commands\RS\Poslovnikontakt\profileAndLinks;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;

class VacuumingPoslovnikontaktCommand extends Command
{
    /**
     * Command config
    */
    protected function configure() : void
    {
        $this->setName('rs:vacuuming-28')
            ->setDescription('Starts download from http://www.privredni-imenik.com')
            ->setHelp('This command allow you start the script')
            ->addOption('url', 'u', InputOption::VALUE_REQUIRED, 'needed url for parsing');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * Main parsed process (start stream)
    */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $guzzle = new GuzzleWrap();
        $crawler = new Crawler($guzzle->getContent($input->getOption('url')));

        $result =
            trim($this->getCompanyName($crawler)) . '}##{' .
            trim($this->getStreet($crawler)) . '}##{' .
            trim($this->getCity($crawler)) . '}##{' .
            trim($this->getPostal($crawler)) . '}##{' .
            trim($this->getPhone($crawler)) . '}##{' . PHP_EOL;

        var_dump($result);
        $this->writeToFile($result);
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getCompanyName(Crawler $crawler) : string
    {
        try{
            return $crawler->filter('.entry-header > h1 > a')->text();
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getStreet(Crawler $crawler) : string
    {
        try{
            $filter = $crawler->filter('.address + .data')->text();
            if(strpos($filter, ',')){
                $filter = explode(',', $filter);
                return $filter[0];
            }
            return $filter;
        }catch (\Exception $e){
            return 'Street';
        }
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getPostal(Crawler $crawler) : string
    {
        try{
            return '';
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getCity(Crawler $crawler) : string
    {
        try{
            $filter = $crawler->filter('.address + .data')->text();
            if(strpos($filter, ',')){
                $filter = explode(',', $filter);
                return $filter[1];
            }
            return $filter;
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getPhone(Crawler $crawler) : string
    {
        try{
            $filter = $crawler->filter('.entry-content')->filter('p')->eq(1)->text();
            $filter = explode('Telefon:', $filter);
                if(strpos($filter[1clear
            ], 'Viber:')){
                    $filter = explode('Viber:', $filter);
                    return $filter[0];
                }
            return preg_replace('/[\D]/', '', $filter[1]);

        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param string $str
     * Writes to file
     */
    public function writeToFile(string $str) : void
    {
        $stream = fopen('parsed3.csv', 'a');
        fwrite($stream, $str);
        fclose($stream);
    }
}