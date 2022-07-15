<?php
    function displayError($toggle) {
        if ($toggle == true) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
        }
        else {
            ini_set("display_errors", "off");
            error_reporting(0);
        }
    }
    
    function ReturnError()
    {
        http_response_code(404);
        $template = new Template();
        $template->IncludeFile("error");
    }

    function SecureHash($string)
    {
        return sha1(sha1(md5($string)) . __HASH_SALT__);
    }

    function AESEncrypt($str, $key = '')
    {
        if (!$key) {
            return false;
        }
        return base64_encode(openssl_encrypt($str, "AES-256-CBC", $key, true, str_repeat(chr(0), 16)));
    }

    function AESDecrypt($str, $key = '')
    {
        if (!$key) {
            return false;
        }
        return openssl_decrypt(base64_decode($str), "AES-256-CBC", $key, true, str_repeat(chr(0), 16));
    }

    function Redirect($page)
    {
        if (substr($page, 0, 1) == "/") {
            header("Location: /");
        }

        header("Location: /" . $page);
        exit;
    }

    function GenerateString($length)
    {
        $characters  = "0123456789";
        $characters .= "abcdefghijklmnopqrstuvwxyz";
        $characters .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $characters .= "_";

        $string_generated = "";

        $nmr_loops = $length;
        while ($nmr_loops--)
        {
            $string_generated .= $characters[mt_rand(0, strlen($characters) - 1)];
        }
        return $string_generated;
    }
    
    function GenerateUUID() {
        $data = random_bytes(16);
        assert(strlen($data) == 16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
?>