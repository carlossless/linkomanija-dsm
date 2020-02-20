<?php

namespace Linkomanija;

class WebClient
{
    private $base_url = 'https://www.linkomanija.net';
    private $search_path = "/browse.php";
    private $login_path = "/takelogin.php";
    private $cookie_jar_path = '/tmp/dlm_linkomanija.cookie';

    public function setup_search_request($curl, $search_query)
    {
        $query_string = http_build_query(array(
            'search' => $search_query,
        ));
        $url = "$this->base_url$this->search_path?$query_string";

        $this->setup_curl_defaults($curl);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_REFERER, "$this->base_url/$this->search_path");
    }

    public function login($username, $password)
    {
        if (file_exists($this->cookie_jar_path)) {
            unlink($this->cookie_jar_path);
        }

        $login_form_data = http_build_query(array(
            'username' => $username,
            'password' => $password,
            'login_cookie' => '1',
            'commit' => 'Prisijungti',
        ));

        $curl = curl_init();
        $this->setup_curl_defaults($curl);
        curl_setopt($curl, CURLOPT_URL, "$this->base_url$this->login_path");
        curl_setopt($curl, CURLOPT_REFERER, "$this->base_url/login.php");
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $login_form_data);
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function generate_rss_link()
    {
        $rss_feed_generation_data = http_build_query(array(
            'feed' => 'dl',
        ));

        $curl = curl_init();
        $this->setup_curl_defaults($curl);
        curl_setopt($curl, CURLOPT_URL, "$this->base_url/getrss.php");
        curl_setopt($curl, CURLOPT_REFERER, "$this->base_url/getrss.php");
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $rss_feed_generation_data);
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    private function setup_curl_defaults($curl)
    {
        $headers = array(
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*;q=0.8',
            'Accept-Language: en-us;q=0.7,en;q=0.3',
            'Accept-Encoding: deflate',
            'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.3',
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($curl, CURLOPT_USERAGENT, DOWNLOAD_STATION_USER_AGENT);
        curl_setopt($curl, CURLOPT_COOKIEJAR, $this->cookie_jar_path);
        curl_setopt($curl, CURLOPT_COOKIEFILE, $this->cookie_jar_path);
    }
}
