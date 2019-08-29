<?php

namespace Commands\RS\Poslovne\profileAndLinks;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Input\InputOption;
use Wraps\GuzzleWrap;

class VacuumingPoslovneCommand extends Command
{
    /**
     * Command config
    */
    protected function configure() : void
    {
        $this->setName('rs:vacuuming-20')
            ->setDescription('Starts download from http://www.privredni-imenik.com')
            ->setHelp('This command allow you start the script')
            ->addOption('url', 'u', InputOption::VALUE_REQUIRED, 'needed url for parsing');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
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
    protected function getTotalRecords(Crawler $crawler) : string
    {
        try{
            return 'PLACE FOR LOGICK';
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getCategory(Crawler $crawler) : string
    {
        try{
            return 'PLACE FOR LOGICK';
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getCompanyName(Crawler $crawler) : string
    {
        try{
            return $crawler->filter('.mainpagec > h1')->text();
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
            $filter = $crawler->filterXPath("//ul[@class='details']")->filter('li')->eq(2)->html();
            $filter = explode('</span>', $filter);
            return $filter[1];
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getPostal(Crawler $crawler) : string
    {
        try{
            $filter = $crawler->filterXPath("//ul[@class='details']")->filter('li')->eq(3)->html();
            $filter = explode('</span>', $filter);
            return $filter[1];        }catch (\Exception $e){
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
            $filter = $crawler->filterXPath("//ul[@class='details']")->filter('li')->eq(4)->html();
            $filter = explode('</span>', $filter);
            return $filter[1];
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
            $filter = $crawler->filterXPath("//ul[@class='details']")->filter('li')->eq(5)->html();
            $filter = explode('</span>', $filter);

            if(strpos($filter[1], ',')){
                $filter = explode(',', $filter[1]);
                return preg_replace('/[\D]/', '', $filter[0]);
            }

            return preg_replace('/[\D]/', '', $filter[1]);
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getEmail(Crawler $crawler) : string
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
    protected function getSite(Crawler $crawler) : string
    {
        try{
            return '';
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
        $stream = fopen('parsed.csv', 'a');
        fwrite($stream, $str);
        fclose($stream);
    }
}