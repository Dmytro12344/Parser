<?php


namespace Commands\CZ\Csfirmy\notAsinc;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Wraps\GuzzleWrap;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;

class CsfirmyParserCommand extends Command
{
    /**
     * Command config
     */
    protected function configure() : void
    {
        $this->setName('cz:start-13')
            ->setDescription('Starts download from www.csfirmy.cz')
            ->setHelp('This command allow you start the script');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * Main parsed process (start stream)
     */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $guzzle = new GuzzleWrap();
        $categories = file('web/Commands/CZ/Csfirmy/list.txt', FILE_SKIP_EMPTY_LINES);

        foreach($categories as $key => $category){


            $totalPages = $this->getTotalPages($category);

            for($i = 1; $i <= $totalPages; $i++) {

                try {
                    $link = "https://www.csfirmy.cz/vyhledavani/$i?q=" . urldecode(trim($category));
                    $crawler = new Crawler($guzzle->getContent(trim($link)));

                    for($j = 0; $j < 9999; $j++){
                    $uri = 'https://www.csfirmy.cz' . $this->getProfileLink($crawler, $j);

                    $crawlerHelper = new Crawler($guzzle->getContent($uri));
                        $result = array_values([
                            'category' => trim($this->getCategory($crawlerHelper)),
                            'name' => trim($this->getCompanyName($crawlerHelper)),
                            'street' => trim($this->getAddress($crawlerHelper)),
                            'postal' => trim($this->getPostal($crawlerHelper)),
                            'city' => trim($this->getCity($crawlerHelper)),
                            'phone' => trim($this->getPhone($crawlerHelper)),
                            'email' => trim($this->getEmail($crawlerHelper)),
                            'site' => trim($this->getSite($crawlerHelper)),
                        ]);

                        var_dump($result);
                        $this->writeToFile([$result]);
                    }
                }
                catch (\Exception $e){
                    echo "\n\n\n\t\t\t\t\t" . $e->getMessage() . " $category\n\n\n\n";
                    continue;
                }

            }
        }

    }

    protected function getCategory(Crawler $crawler) : string
    {
        try {
            return $crawler->filter('.col-md-12 > h2')->text();
        }
        catch (\Exception $e){
            return '';
        }
    }

    protected function getCompanyName(Crawler $crawler) : string
    {
        try{
            return $crawler->filter('.col-md-12 > h1')->text();
        }
        catch (\Exception $e){
            return '';
        }
    }

    public function getPhone(Crawler $crawler)
    {
        try {
            $filter = $crawler->filterXPath("//ul[@class='table']")->eq(1);
            $phone = $filter->filter('li')->eq(2)->text();
            $phone = str_replace(' ', '', $phone);
            $clearPhone = explode('-', $phone);

            if (!empty($clearPhone[1])) {
                $phone = $clearPhone[0];
            }

            $phone = str_replace('+', '', $phone);

            if (is_numeric($phone)) {
                return $phone;
            }

            return '';

        }catch (\Exception $e){
            return '';
        }
    }

    protected function getEmail(Crawler $crawler) : string
    {
        try {

            $filter = $crawler->filterXPath("//ul[@class='table']")->eq(1);
            $email = trim($filter->filter('li > a')->eq(0)->text());
            $position = strrpos($email, '@');

            if ($position !== false) {
                return $email;
            }

            return '';
        } catch (\Exception $e){
            return '';
        }
    }

    protected function getSite(Crawler $crawler) : string
    {
        try {
            $filter = $crawler->filterXPath("//ul[@class='table']")->eq(1);

            if ($filter->filter('li > a')->eq(1)->count() > 0) {

                $site = trim($filter->filter('li > a')->eq(1)->text());
                $position = strrpos($site, '@');

                if ($position === false) {
                    return $site;
                }
            }

            if ($filter->filter('li > a')->eq(0)->count() > 0) {

                $site = trim($filter->filter('li > a')->eq(0)->text());
                $position = strrpos($site, '@');

                if ($position === false) {
                    return $site;
                }
            }

            return '';
        }
        catch(\Exception $e){
            return '';
        }
    }

    protected function getAddress(Crawler $crawler) : string
    {
        try {
            $filter = $crawler->filterXPath("//ul[@class='table']")->eq(1);
            return $filter->filter('li')->eq(0)->text();
        }catch (\Exception $e){
            return '';
        }
    }

    protected function getPostal(Crawler $crawler) : string
    {
        try {

            $filter = $crawler->filterXPath("//ul[@class='table']")->eq(1);

            if ($filter->filter('li')->eq(1)->count() > 0) {
                $postlANDcity = trim($filter->filter('li')->eq(1)->text());

                $clearPostal = explode(' ', $postlANDcity);

                if (is_numeric($clearPostal[0]) && is_numeric($clearPostal[1])) {
                    return $clearPostal[0] . $clearPostal[1];
                }

                if (is_numeric($clearPostal[0]) && !is_numeric($clearPostal[1])) {
                    return $clearPostal[0];
                }

                if (!is_numeric($clearPostal[0] && is_numeric($clearPostal[1]))) {
                    return $clearPostal[1] . ($clearPostal[2] ?? '');
                }
            }

            return '';
        } catch (\Exception $e){
            return '';
        }
    }

    protected function getCity(Crawler $crawler) : string
    {
        try {
            $filter = $crawler->filterXPath("//ul[@class='table']")->eq(1);

            if ($filter->filter('li')->eq(1)->count() > 0) {

                $postlANDcity = trim($filter->filter('li')->eq(1)->text());
                $city = '';
                $clearCity = explode(' ', $postlANDcity);

                if (!is_numeric($clearCity[2])) {
                    for ($i = 2; $i < @count($clearCity); $i++) {
                        $city .= $clearCity[$i] . ' ';
                    }
                    return $city;
                }

                if (!is_numeric($clearCity[0])) {
                    for ($i = 0; $i <= @count($clearCity); $i++) {
                        $city .= $clearCity[$i] . ' ';
                    }
                    return $city;
                }
            }

            return '';
        }
        catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param string $category
     * @return int
     * Returns total pages from site
     */
    protected function getTotalPages(string $category) : int
    {
        $guzzle = new GuzzleWrap();
        $link = 'https://www.csfirmy.cz/vyhledavani/1?q=' . urldecode(trim($category));

        try {
            $crawler = new Crawler($guzzle->getContent(trim($link)));

            $liPosition = $crawler->filter('.pagination')->children()->count();
            $getLink = $crawler->filter('.pagination > li')->eq($liPosition - 1)->filter('a')->attr('href');
            $totalPages = substr($getLink, -1);
            return (int)$totalPages;
        } catch (\Exception $e) {
            return 1;
        }
    }

    /**
     * @param Crawler $crawler
     * @return int
     * Returns total records from page
     */
    public function getTotalRecords(Crawler $crawler) : int
    {
        try {
            return $crawler->filter('#containerIAS > .company-item')->count();
        }
        catch (\Exception $e){
            return 0;
        }
    }

    protected function getProfileLink(Crawler $crawler, int $k) : string
    {
        $filter = $crawler->filter('.shadow > .title')->eq($k);
        return trim($filter->filter( 'h2 > a')->attr('href'));
    }

    public function getProfile(int $total, Crawler $crawler) : \Generator
    {
        $url = 'https://www.zivefirmy.cz';

        for ($k = 0; $k < $total; $k++) {
            $uri = $url . $this->getProfileLink($crawler, $k);
            yield new Request('GET', $uri);
        }
    }


    /**
     * @param array $arr
     * Writes to file
     */
    public function writeToFile(array $arr) : void
    {
        $stream = fopen('parsed3.csv', 'a');
        foreach($arr as $item) {
            fputcsv($stream, $item, '|');
        }
        fclose($stream);
    }


}