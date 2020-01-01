<?php

declare(strict_types=1);

namespace Nekudo\EasyTimebutler\Services\Timebutler;

class Timebutler
{
    private $tmpDir;

    public function setTmpDir(string $pathToTmpDir): void
    {
        if (!file_exists($pathToTmpDir)) {
            throw new \RuntimeException('Invalid path to tmp-dir provided. Check timebutler config.');
        }
        $this->tmpDir = rtrim($pathToTmpDir, '/');
    }

    public function login(string $email, string $password): bool
    {
        $requestParams = http_build_query([
            'ha' => 'login',
            'ac' => 1,
            'afteroauth' => 0,
            'login' => $email,
            'passwort' => $password,
            'passwortunhidden' => '',
            'keeplogin' => 1,
        ]);

        $cookieFile = $this->tmpDir . '/' . sha1($email) . '.txt';

        $ch = curl_init('https://timebutler.de/do');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestParams);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_REFERER, 'https://timebutler.de/login/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // on response code other than 200 something went wrong
        if ($status !== 200) {
            return false;
        }

        // check if logout link is present to be sure we are logged in
        if (strpos($response, 'do?ha=login&ac=2') === false) {
            return false;
        }

        return true;
    }
}
