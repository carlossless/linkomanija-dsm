<?php

require_once 'Linkomanija/WebClient.php';
require_once 'Linkomanija/SearchResponseParser.php';
require_once 'Linkomanija/PasskeyParser.php';
require_once 'Linkomanija/PasskeyStore.php';

class SynologyDLMSearchLinkomanija
{
    private $base_url = 'https://www.linkomanija.net';

    public function __construct($web_client, $passkey_store)
    {
        $this->web_client = ($web_client ?: new Linkomanija\WebClient);
        $this->passkey_store = ($passkey_store ?: new Linkomanija\PasskeyStore);
    }

    public function prepare($curl, $search_query, $username, $password)
    {
        $this->web_client->setup_search_request($curl, $search_query);

        if ($username !== null && $password !== null) {
            $this->VerifyAccount($username, $password);
        }
    }

    public function parse($plugin, $response)
    {
        $passkey = $this->passkey_store->get();
        $parser = new Linkomanija\SearchResponseParser($response, $passkey);

        $res = 0;
        foreach ($parser->entries() as $entry) {
            $download_url = "$this->base_url/$entry->download&" . http_build_query(array('passkey' => $passkey));
            $page_url = "$this->base_url/$entry->page";

            $plugin->addResult(
                $entry->title,
                $download_url,
                $entry->size,
                $entry->datetime,
                $page_url,
                $entry->hash,
                $entry->seeds,
                $entry->leeches,
                $entry->category
            );
            $res++;
        }
        return $res;
    }

    public function VerifyAccount($username, $password)
    {
        $response = $this->web_client->login($username, $password);

        if (false !== strpos($response, 'logout.php')) {
            $this->fetch_and_save_passkey();
            return true;
        }

        return false;
    }

    private function fetch_and_save_passkey()
    {
        $response = $this->web_client->generate_rss_link();
        $parser = new Linkomanija\PasskeyParser($response);

        $passkey = $parser->passkey();
        if (strlen($passkey) > 0) {
            $this->passkey_store->set($passkey);
            return true;
        }

        return false;
    }
}
