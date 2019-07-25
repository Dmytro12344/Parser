<?php


namespace Commands\PL\Ptc\asyncWithProfile;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Wraps\GuzzleWrap;

class VacuumingProfileCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('pl:vacuuming-1')
            ->setDescription('Starts download')
            ->setHelp('This command allow you start the script')
            ->addOption('url', 'u', InputOption::VALUE_REQUIRED, 'needed url for parsing');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $guzzle = new GuzzleWrap();

        try {
            $crawler = new Crawler($guzzle->getContent($input->getOption('url')));

            $result = array_values([
                'category' => trim($this->getCategory($crawler)),
                'name' => trim($this->getCompanyName($crawler)),
                'address' => trim($this->getStreet($crawler)),
                'postal' => trim($this->getPostal($crawler)),
                'city' => trim($this->getCity($crawler)),
                'phone' => $this->getPhone($crawler),
                'email' => $this->getEmail($crawler),
                'site' => $this->getSite($crawler),
            ]);

            var_dump($result);
            $this->writeToFile([$result]);

        }catch(\Exception $e){
            echo $e->getMessage();
        }
    }

    protected function getCategory(Crawler $crawler) : string
    {
        try{
            return $crawler->filter('ol > li')->eq(2)->filter('a > span')->text();
        }catch (\Exception $e){
            return '';
        }
    }

    protected function getEmail(Crawler $crawler) : string
    {
        try{
            return $crawler->filter('.call--email > a > span')->attr('title');
        } catch (\Exception $e){
            return '';
        }
    }

    protected function getSite(Crawler $crawler) : string
    {
        try{
            return $crawler->filter('.www--full')->text();
        } catch (\Exception $e){
            return '';
        }
    }


    protected function getCompanyName(Crawler $crawler): string
    {
        try {
            return $crawler->filterXPath("//h1[@class='company-name']")->text();
        } catch (\Exception $e) {
            return '';
        }
    }


    protected function getStreet(Crawler $crawler) : string
    {
        try {
            $filter =  $crawler->filterXPath("//span[@itemprop='streetAddress']")->text();
            if(strpos($filter, ',')){
                $street = explode(',', $filter);
                return $street[0];
            }
            return $filter;

        } catch (\Exception $e) {
            return '';
        }
    }

    public function getCity(Crawler $crawler) : string
    {
        try {
            return $crawler->filterXPath("//span[@itemprop='addressRegion']")->text();
        } catch (\Exception $e) {
            return '';
        }
    }

    public function getPostal(Crawler $crawler) : string
    {
        try {
            return preg_replace('/[^0-9]/', '', $crawler->filterXPath("//span[@itemprop='postalCode']")->text());
        } catch (\Exception $e) {
            return '';
        }
    }

    protected function getPhone(Crawler $crawler) : string
    {
        try{
            return $crawler->filterXPath("//span[@itemprop='telephone']")->text();
        } catch (\Exception $e){
            return '';
        }
    }

    protected function getTotalRecords(Crawler $crawler) : int
    {
        try{
            return $crawler->filter('.list-sel')->count();
        }
        catch (\Exception $e) {
            return 0;
        }    }

    /**
     * @param array $arr
     * Writes to file
     */
    public function writeToFile(array $arr) : void
    {
        $stream = fopen('parsed.csv', 'a');
        foreach($arr as $item) {
            fputcsv($stream, $item, '|');
        }
        fclose($stream);
    }
}