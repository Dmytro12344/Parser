<?php

namespace Commands\RS\Navidiku\profileAndLinks;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;

class VacuumingNavidikuCommand extends Command
{
    /**
     * Command config
    */
    protected function configure() : void
    {
        $this->setName('rs:vacuuming-23')
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

        /*for($i = 0; $i< $this->getTotalLinks($crawler); $i++){
            $result = $this->getLinks($crawler, $i);
            $this->writeToFile($result);
        }*/
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
     *
     * Collect links from pages
     */
    protected function getLinks(Crawler $crawler, int $k) : string
    {
        return 'https://www.navidiku.rs/firme/' . $crawler->filter('.no-style > li')->eq($k)->filter('a')->attr('href') . PHP_EOL;
    }

    /**
     * @param Crawler $crawler
     * @return int
     *
     * Return total links from page
     */
    protected function getTotalLinks(Crawler $crawler) : int
    {
        return $crawler->filter('.no-style > li')->count();
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getCompanyName(Crawler $crawler) : string
    {
        try{
            return $crawler->filter('.header > h1 > a')->text();
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
            $filter = $crawler->filter('.address > .no-style')->html();
            $filter = explode('<li>Ulica: <b>', $filter);
            $filter = explode('</b>', $filter[1]);

            return $filter[0];
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
            $filter = $crawler->filter('.address > .no-style')->html();
            $filter = explode('<li>Grad: <b>', $filter);
            $filter = explode('</b>', $filter[1]);

            return $filter[0];
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
            return preg_replace('/[\D]/', '', $crawler->filter('.company-phone')->attr('data-tel'));
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
        $stream = fopen('parsed1.csv', 'a');
        fwrite($stream, $str);
        fclose($stream);
    }
}