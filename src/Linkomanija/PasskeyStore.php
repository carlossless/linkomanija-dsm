<?php

namespace Linkomanija;

class PasskeyStore
{
    private $passkey_file_path = '/tmp/dlm_linkomanija.passkey';

    public function get()
    {
        if (file_exists($this->passkey_file_path)) {
            return trim(file_get_contents($this->passkey_file_path));
        }
        return null;
    }

    public function set($passkey)
    {
        file_put_contents($this->passkey_file_path, "$passkey\n");
    }
}
