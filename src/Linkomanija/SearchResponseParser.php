<?php

namespace Linkomanija;

require_once 'TorrentEntry.php';

class SearchResponseParser
{
    private $dom, $xpath, $passkey;

    public function __construct($html)
    {
        $this->dom = new \DomDocument;
        libxml_use_internal_errors(true);
        $this->dom->loadHTML($html);
        $this->xpath = new \DomXPath($this->dom);
    }

    public function entries()
    {
        $nodes = $this->xpath->query("//table/tr[count(td)=9 and position()>1]");
        foreach ($nodes as $i => $node) {
            $rows = $this->xpath->query("descendant::td", $node);

            $title = $this->extract_title($this->xpath, $rows[1]);
            $download = $this->extract_download_url($this->xpath, $rows[1]);
            $size = $this->extract_size($this->xpath, $rows[5]);
            $datetime = $this->extract_datetime($this->xpath, $rows[4]);
            $page = $this->extract_page_url($this->xpath, $rows[1]);
            $hash = "unknown";
            $seeds = $this->extract_seeds($this->xpath, $rows[7]);
            $leeches = $this->extract_leeches($this->xpath, $rows[8]);
            $category = $this->extract_category($this->xpath, $rows[0]);

            yield new TorrentEntry($title, $download, $size, $datetime, $page, $hash, $seeds, $leeches, $category);
        }
    }

    private function extract_title($xpath, $node)
    {
        return $xpath->query("descendant::a", $node)[0]->textContent;
    }

    private function extract_download_url($xpath, $node)
    {
        return $xpath->query("descendant::a/@href", $node)[1]->nodeValue;
    }

    private function extract_size($xpath, $node)
    {
        return $this->convert_size_to_bytes($node->textContent);
    }

    private function extract_datetime($xpath, $node)
    {
        $datetime_nodes = $xpath->query("descendant::text()", $node);
        $date = $datetime_nodes[0]->textContent;
        $time = $datetime_nodes[1]->textContent;
        return date("Y-m-d H:i", strtotime("$date $time"));
    }

    private function extract_page_url($xpath, $node)
    {
        return $xpath->query("descendant::a/@href", $node)[0]->nodeValue;
    }

    private function extract_seeds($xpath, $node)
    {
        return intval($node->textContent);
    }

    private function extract_leeches($xpath, $node)
    {
        return intval($node->textContent);
    }

    private function extract_category($xpath, $node)
    {
        return $xpath->query("descendant::a/img/@title", $node)[0]->nodeValue;
    }

    private function convert_size_to_bytes($size_string)
    {
        if (strlen($size_string) < 3) {
            return 0;
        }

        $size = doubleval(substr($size_string, 0, -2));
        $size_dim = substr($size_string, -2);

        switch (trim($size_dim)) {
            case 'KB':
                $size = $size * 1024;
                break;
            case 'MB':
                $size = $size * 1024 * 1024;
                break;
            case 'GB':
                $size = $size * 1024 * 1024 * 1024;
                break;
            case 'TB':
                $size = $size * 1024 * 1024 * 1024 * 1024;
                break;
        }
        return intval($size);
    }
}
