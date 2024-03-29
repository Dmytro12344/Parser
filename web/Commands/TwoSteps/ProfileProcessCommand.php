<?php

namespace Commands\TwoSteps;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Wraps\GuzzleWrap;

/**
 * Class ProfileProcessCommand
 * @package Commands\Process
 * Needed if there are profiles
 */
class ProfileProcessCommand extends Command
{
    /**
     * Command configuration
     */
    protected function configure() : void
    {
        $this->setName('app:download-profile-content')
            ->setDescription('Starts download')
            ->setHelp('This command allow you start the script')
            ->addOption('url', 'u' , InputOption::VALUE_REQUIRED);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * Executes parse and saves it to file
     */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $guzzle = new GuzzleWrap();
        $crawler = new Crawler($guzzle->getContent($input->getOption('url')));

        /** Set parameters */
        $name = $this->filterContent($crawler, '.container-fluid > h1');
        $phone = $this->XPathContent($crawler, "//a[@class='dotted']");
        $address = $this->XPathContent($crawler, 'span');
        $city =  $this->XPathContent($crawler, 'span');
        $postal = $this->XPathContent($crawler, 'span');

        $str = [trim($name), trim($city), trim($address), trim($postal), trim($phone)];

        /** Write data to file */
        $this->writeToFile($str);
    }

    /**
     * @param Crawler $crawler
     * @param string $xPath
     * @return string
     */
    public function XPathContent(Crawler $crawler, string $xPath) : string
    {
        $filter = $crawler->filterXPath($xPath);

        if($filter->count() > 0){
            return $filter->text();
        }
        return '';
    }

    /**
     * @param $crawler
     * @param string $filter ('.class > #id ')
     * @return string
     * returns filtered content
     */
    public function filterContent(Crawler $crawler, string $filter) : string
    {
        return $crawler->filter($filter)->text();
    }

    /**
     * @param array $arr
     */
    public function writeToFile(array $arr) : void
    {
        $stream = fopen('parsed.csv', 'a+');
        fputcsv($stream, $arr);
        fclose($stream);
    }

}