<?php

require_once 'src/SynologyDLMSearchLinkomanija.php';

define(
    "DOWNLOAD_STATION_USER_AGENT",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.106 Safari/537.36"
);

class FakeSynologyDLMSearchPlugin {
    public function addResult() {
        $arg_list = func_get_args();
        echo join(' ', $arg_list) . "\n";
    }
}

$username = $_ENV['LINKOMANIJA_USERNAME'];
$password = $_ENV['LINKOMANIJA_PASSWORD'];

if (strlen($username) == 0) {
    echo "LINKOMANIJA_USERNAME must be provided\n";
    exit(1);
}

if (strlen($password) == 0) {
    echo "LINKOMANIJA_PASSWORD must be provided\n";
    exit(1);
}

$dlm = new SynologyDLMSearchLinkomanija(null, null);

if (!$dlm->VerifyAccount($username, $password)) {
    echo "Could not verify user credentials\n";
    exit(1);
}

$curl = curl_init();
$dlm->prepare($curl, 'test', $username, $password);
$response = curl_exec($curl);
curl_close($curl);

echo "Pulling entries:\n";
$result = $dlm->parse(new FakeSynologyDLMSearchPlugin, $response);
echo "Finished pulling $result results\n";
