<?php

namespace Linkomanija;

class PasskeyParser
{
    private $dom, $xpath;

    public function __construct($html)
    {
        libxml_use_internal_errors(true);

        $this->dom = new \DomDocument;
        $this->dom->loadHTML($html);
        $this->xpath = new \DomXPath($this->dom);
    }

    public function passkey()
    {
        $node = $this->xpath->query("//textarea")[0];
        $rss_url = $node->textContent;

        $rss_url_parts = parse_url($rss_url);
        parse_str($rss_url_parts['query'], $query);
        $passkey = $query['passkey'];
        return $passkey;
    }
}
