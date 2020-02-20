<?php

namespace spec;

use SynologyDLMSearchLinkomanija;
use Linkomanija\PasskeyStore;
use Linkomanija\WebClient;
use PhpSpec\ObjectBehavior;

define("DOWNLOAD_STATION_USER_AGENT", "fakeclient");

interface SynologyDLMSearchPlugin
{
    public function addResult($title, $download, $size, $datetime, $page, $hash, $seeds, $leeches, $category);
}

class SynologyDLMSearchLinkomanijaSpec extends ObjectBehavior
{
    function let(WebClient $web_client, PasskeyStore $passkey_store)
    {
        $this->beConstructedWith($web_client, $passkey_store);
    }

    function it_prepares_for_the_search_request(WebClient $web_client, PasskeyStore $passkey_store)
    {
        $username = 'testuser';
        $password = 'testpass';
        $query = 'prince test';

        $login_response = file_get_contents('spec/fixtures/login_response.html');
        $web_client->login($username, $password)->willReturn($login_response);
        $rss_link_response = file_get_contents('spec/fixtures/generate_rss_response.html');
        $web_client->generate_rss_link()->willReturn($rss_link_response);

        $curl = curl_init();
        $this->prepare($curl, $query, $username, $password);
        curl_close($curl);

        $web_client->setup_search_request($curl, $query)->shouldHaveBeenCalled();
        $web_client->login($username, $password)->shouldHaveBeenCalled();
        $web_client->generate_rss_link()->shouldHaveBeenCalled();
        $passkey_store->set('fake_passkey')->shouldHaveBeenCalled();
    }

    function it_verifies_the_user(WebClient $web_client, PasskeyStore $passkey_store)
    {
        $username = 'testuser';
        $password = 'testpass';

        $login_response = file_get_contents('spec/fixtures/login_response.html');
        $web_client->login($username, $password)->willReturn($login_response);
        $rss_link_response = file_get_contents('spec/fixtures/generate_rss_response.html');
        $web_client->generate_rss_link()->willReturn($rss_link_response);

        $this->VerifyAccount($username, $password)->shouldReturn(true);

        $web_client->login($username, $password)->shouldHaveBeenCalled();
        $web_client->generate_rss_link()->shouldHaveBeenCalled();
        $passkey_store->set('fake_passkey')->shouldHaveBeenCalled();
    }

    function it_parses_the_response(PasskeyStore $passkey_store, SynologyDLMSearchPlugin $plugin)
    {
        $response = file_get_contents('spec/fixtures/search_response.html');
        $passkey_store->get()->willReturn('test_passkey');

        $this->parse($plugin, $response)->shouldReturn(15);

        $plugin->addResult(
            "Bugs Bunny Big Top Bunny 1959 VHSRip x264 AAC-BTG",
            "https://www.linkomanija.net/download.php?id=357346&name=Bugs.Bunny.Big.Top.Bunny.1959.VHSRip.x264.AAC-BTG.mkv.torrent&passkey=test_passkey",
            782552268,
            "2014-06-18 22:04",
            "https://www.linkomanija.net/details?357346.Bugs_Bunny_Big_Top_Bunny_1959_VHSRip_x264_AAC-BTG",
            "unknown",
            1,
            0,
            "Movies LT"
        )->shouldHaveBeenCalled();
    }
}
