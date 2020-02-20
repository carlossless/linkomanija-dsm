<?php

namespace Linkomanija;

class TorrentEntry
{
    public $title, $download, $size, $datetime, $page, $hash, $seeds, $leeches, $category;

    public function __construct($title, $download, $size, $datetime, $page, $hash, $seeds, $leeches, $category)
    {
        $this->title = $title;
        $this->download = $download;
        $this->size = $size;
        $this->datetime = $datetime;
        $this->page = $page;
        $this->hash = $hash;
        $this->seeds = $seeds;
        $this->leeches = $leeches;
        $this->category = $category;
    }
}
