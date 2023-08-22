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
                $this->content = file_get_contents($this->url);
                $cookies = array();
                foreach ($http_response_header as $hdr) {
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
                'phone' => trim($node->childNodes[5]->nodeValue),
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