<?php

namespace Od\EczaneApi;

class Scraper
{
    /** @var \DOMDocument */
    protected $dom;
    /** @var \DOMXPath */
    protected $xpath;
    /** @var string */
    protected $url;
    /** @var string */
    protected $content;
    /** @var string */
    protected $baseUrl;
    /**
     * @var array
     */
    protected $cookies = [];

    /**
     * @param $url
     * @param bool $postContent
     * @return $this
     * @throws \Exception
     */
    public function setUrl($url, $postContent = false)
    {
        $this->url = $url;
        $this->loadContent($postContent);
        return $this;
    }

    /**
     * @param array $cookies
     * @return $this
     */
    public function setCookies($cookies)
    {
        $this->cookies = $cookies;
        return $this;
    }

    /**
     * @return array
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * Check url parameter is valid parameter, then load or throw exception
     * @return void
     * @throws \Exception
     */
    public function loadContent($postContent = false)
    {
        if($url = parse_url($this->url)){
            if(!$postContent) {
                $responseHeaders = [];
                $maxRetries = 3;
                for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                    $responseHeaders = [];
                    $curl = curl_init();
                    curl_setopt_array($curl, [
                        CURLOPT_URL => $this->url,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_CONNECTTIMEOUT => 10,
                        CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_HTTPHEADER => [
                            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:109.0) Gecko/20100101 Firefox/117.0',
                            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                            'Accept-Language: en-US,tr;q=0.5',
                        ],
                        CURLOPT_HEADERFUNCTION => function($ch, $header) use (&$responseHeaders) {
                            $responseHeaders[] = $header;
                            return strlen($header);
                        },
                    ]);
                    $this->content = curl_exec($curl);
                    if ($this->content !== false) {
                        break;
                    }
                    $error = curl_error($curl);
                    if ($attempt === $maxRetries) {
                        throw new \Exception('cURL error after ' . $maxRetries . ' attempts: ' . $error);
                    }
                    sleep($attempt);
                }

                $cookies = [];
                foreach ($responseHeaders as $hdr) {
                    if (preg_match('/^Set-Cookie:\s*([^;]+)/', $hdr, $matches)) {
                        parse_str($matches[1], $tmp);
                        $cookies += $tmp;
                    }
                }
                $this->setCookies($cookies);
            } else {
                $this->content = $postContent;
            }
            $this->baseUrl = $url['scheme'] . '://' . $url['host'];
        } else {
            throw new \Exception('Invalid URL');
        }
    }

    /**
     * Get token with xpath query
     * @return string
     */
    public function getToken()
    {
        $nodes = $this->xpath->query('/html/body/@data-token');
        foreach($nodes as $node){
            return trim($node->nodeValue);
        }
        return '';
    }

    /**
     * Get table array using xpath query.
     * @return array
     */
    public function getTable()
    {
        $nodes = $this->xpath->query('//table[@id="searchTable"]/tbody/tr');
        $table = [];
        foreach($nodes as $node){
            $row = [
                'name' => trim($node->childNodes[1]->nodeValue),
                'district' => trim($node->childNodes[3]->nodeValue),
                'address' => trim($node->childNodes[7]->nodeValue),
                'phone' => preg_replace('/[^0-9]/', '', trim($node->childNodes[5]->nodeValue)),
            ];
            $table[] = $row;
        }
        return $table;
    }

    /**
     * @return self
     */
    public function scrape()
    {
        $this->loadDOM();
        $this->loadDOMXPath();
        return $this;
    }

    /**
     * Load HTML dom if not loaded.
     * @return void
     */
    protected function loadDOM()
    {
        if (empty($this->content)) {
            throw new \Exception('No content to parse. The request may have failed.');
        }
        $this->dom = new \DOMDocument();
        @$this->dom->loadHTML($this->content);
    }

    /**
     * Load DOMXPath if not loaded.
     * @return void
     */
    protected function loadDOMXPath()
    {
        $this->xpath = new \DOMXPath($this->dom);
    }
}