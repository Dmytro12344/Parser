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
     * ProfileProcessCommand constructor.
     * Don't using
     */
    public function __construct()
    {
        parent::__construct();
    }

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

        /**
         * Set company name
         */
        $name = $this->filterContent($crawler, '.container-fluid > h1');

        /**
         * Set Phone number
         * If content is'nt empty write ur condition after ?
         * If content is empty write ur condition after :
         */
        $phone = $this->isXPathContent($crawler, 'a', 'dotted', 'class')
            ? $this->XPathContent($crawler, 'a', 'dotted', 'class')
            : '';

        /**
         * Set Address
         * If content is'nt empty write ur condition after ?
         * If content is empty write ur condition after :
         */
        $address = $this->isXPathContent($crawler, 'span', 'streetAddress')
            ? $this->XPathContent($crawler, 'span', 'streetAddress')
            : '';

        /**
         * Set City
         * If content is'nt empty write ur condition after ?
         * If content is empty write ur condition after :
         */
        $city =  $this->isXPathContent($crawler, 'span', 'addressLocality')
            ? $this->XPathContent($crawler, 'span', 'addressLocality')
            : '';

        /**
         * Set Postal (ZIP) Code
         * If content is'nt empty write ur condition after ?
         * If content is empty write ur condition after :
         */
        $postal = $this->isXPathContent($crawler, 'span', 'postalCode')
            ? $this->XPathContent($crawler, 'span', 'postalCode')
            : '';

        /**
         * Write data to file
         */
        $stream = fopen('parsed.csv', 'a+');
        $str = [trim($name), trim($city), trim($address), trim($postal), trim($phone)];
        fputcsv($stream, $str);
        fclose($stream);
    }

    /**
     * @param $crawler
     * @param $tag
     * @param $attrName
     * @param string $attr
     * @return string
     * returns Text from needed HTML tag attribute
     */
    public function XPathContent($crawler, $tag, $attrName, $attr = 'itemprop') : string
    {
        return $crawler->filterXPath("//". $tag ."[@" . $attr ."='" . $attrName . "']")->text();
    }

    /**
     * @param $crawler
     * @param string $filter ('.class > #id ')
     * @return string
     * returns filtered content
     */
    public function filterContent($crawler, string $filter) : string
    {
        return $crawler->filter($filter)->text();
    }

    /**
     * @param $crawler
     * @param $tag
     * @param $attrName
     * @param string $attr
     * @return bool
     * returns bool if HTML tag attribute is not empty and FALSE if empty
     */
    public function isXPathContent($crawler, $tag, $attrName, $attr = 'itemprop') : bool
    {
        return ($crawler->filterXPath("//". $tag ."[@" . $attr ."='" . $attrName . "']")->count() > 0) ? true : false;
    }
}