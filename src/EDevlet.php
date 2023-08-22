<?php

namespace Od\EczaneApi;

class EDevlet
{
    protected $scraper;

    protected $token;

    protected $cookies;

    protected $url = 'https://www.turkiye.gov.tr/saglik-titck-nobetci-eczane-sorgulama?submit';

    public function __construct()
    {
        $this->scraper = new Scraper();
    }
    public function getDataBySelections($cityCode, $date) {
        $this->scraper = $this->scraper->setUrl($this->url);
        $scraped = $this->scraper->scrape();
        $this->token =  $scraped->getToken();
        $this->cookies = $scraped->getCookies();
        $this->scraper = $this->scraper->setUrl($this->url, $this->postByScrapedData($cityCode, $date));
        $scraped = $this->scraper->scrape();
        return $scraped->getTable();
    }

    protected function postByScrapedData($cityCode, $date)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query([
                'plakaKodu' => $cityCode,
                'nobetTarihi' => $date,
                'token' => $this->token,
                'btn' => 'Sorgula',
            ]),
            CURLOPT_HTTPHEADER => array(
                'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:109.0) Gecko/20100101 Firefox/117.0',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language: en-US,tr;q=0.5',
                'Accept-Encoding: gzip, deflate, br',
                'Referer: https://www.turkiye.gov.tr/saglik-titck-nobetci-eczane-sorgulama',
                'Content-Type: application/x-www-form-urlencoded',
                'Origin: https://www.turkiye.gov.tr',
                'Connection: keep-alive',
                'Cookie: '.http_build_query($this->cookies,'','; '),
                'Upgrade-Insecure-Requests: 1',
                'Sec-Fetch-Dest: document',
                'Sec-Fetch-Mode: navigate',
                'Sec-Fetch-Site: same-origin',
                'Sec-Fetch-User: ?1',
                'Pragma: no-cache',
                'Cache-Control: no-cache'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}