<?php


namespace Wraps;

use Symfony\Component\DomCrawler\Crawler;

class WrapPars
{
    public function getPars()
    {
        $html = '
            <!DOCTYPE html>
            <html>
            <body>
                <p class="message">Hello World!</p>
                <div id="td">Hello</div>
                <div id="td">My</div>
                <div class="td">Dear</div>
                <div class="td">Friend</div>
                <p>Hello Crawler!</p>
            </body>
            </html>';


        $crawler = new Crawler($html);
        $td = $crawler->filter('#td');

        echo $td[0];

       /* foreach ($crawler as $domElement) {
           print_r($domElement);
        }*/
    }
}