<?php

declare(strict_types=1);

namespace Nekudo\EasyTimebutler\Services\Timebutler;

class Timebutler
{
    /**
     * @var string $tmpDir
     */
    private $tmpDir;

    /**
     * Sets path to tmp-dir. Used for storing cookie files.
     *
     * @param string $pathToTmpDir
     * @throws TimebutlerException
     */
    public function setTmpDir(string $pathToTmpDir): void
    {
        if (!file_exists($pathToTmpDir)) {
            throw new TimebutlerException('Invalid path to tmp-dir provided. Check timebutler config.');
        }
        $this->tmpDir = rtrim($pathToTmpDir, '/');
    }

    /**
     * Starts the stopclock and returns state of clock.
     *
     * @param string $email
     * @param string $password
     * @return \stdClass
     * @throws TimebutlerException
     */
    public function startClock(string $email, string $password): \stdClass
    {
        // login
        $this->loginIfRequired($email, $password);

        // start clock and return clock state
        $response = $this->doGetRequest([
            'ha' => 'zee',
            'ac' => 101,
            'compid' => '',
            'ajx' => 1,
            '_' => $this->getTimeString(),
        ], $email);
        $clockState = $this->parseClockResponse($response);
        if ($clockState->running !== 1 || $clockState->state !== 1) {
            throw new TimebutlerException('Could not start stopclock. Unexpected response.');
        }

        return $clockState;
    }

    /**
     * Pauses stopclock and returns new stopclock state.
     *
     * @param string $email
     * @param string $password
     * @return \stdClass
     * @throws TimebutlerException
     */
    public function pauseClock(string $email, string $password): \stdClass
    {
        // login
        $this->loginIfRequired($email, $password);

        // pause clock and return clock state
        $response = $this->doGetRequest([
            'ha' => 'zee',
            'ac' => 102,
            'compid' => '',
            'ajx' => 1,
            '_' => $this->getTimeString(),
        ], $email);
        $clockState = $this->parseClockResponse($response);
        if ($clockState->running !== 0 || $clockState->paused !== 1) {
            throw new TimebutlerException('Could not pause stopclock. Unexpected response.');
        }

        return $clockState;
    }

    /**
     * Resumes stopclock and returns new stopclock state.
     *
     * @param string $email
     * @param string $password
     * @return \stdClass
     * @throws TimebutlerException
     */
    public function resumeClock(string $email, string $password): \stdClass
    {
        return $this->startClock($email, $password);
    }

    /**
     * Stops (and saves) stopclock and returns new stopclock state.
     *
     * @param string $email
     * @param string $password
     * @return \stdClass
     * @throws TimebutlerException
     */
    public function stopClock(string $email, string $password): \stdClass
    {
        // login
        $this->loginIfRequired($email, $password);

        // stop clock
        $this->doGetRequest([
            'ha' => 'zee',
            'ac' => 110,
            'compid' => '',
            'ajx' => 1,
            '_' => $this->getTimeString(),
        ], $email);

        // save clock
        $response = $this->doGetRequest([
            'ha' => 'zee',
            'ac' => 103,
            'ignorePausReg' => 1,
            'compid' => '',
            'ajx' => 1,
            '_' => $this->getTimeString(),
        ], $email);
        $clockState = $this->parseClockResponse($response);
        if ($clockState->running !== 0 || $clockState->paused !== 0) {
            throw new TimebutlerException('Could not stop stopclock. Unexpected response.');
        }

        return $clockState;
    }

    /**
     * Requests current login and stopclock state from timebutler.
     *
     * @param string $email
     * @return array
     * @throws TimebutlerException
     */
    public function getState(string $email): array
    {
        $state = [
            'logged_in' => false,
            'clock_state' => false,
        ];

        $response = $this->doGetRequest([], $email);
        $state['logged_in'] = (strpos($response, 'do?ha=login&ac=2') !== false);
        if ($state['logged_in'] === false) {
            return $state;
        }

        // parse clock state from response
        $pattern = '/data-state="(?<state>\d)"\s+';
        $pattern .= 'data-running="(?<running>\d)"\s+';
        $pattern .= 'data-paused="(?<paused>\d)"\s+';
        $pattern .= 'data-pausesec="(?<pausesec>\d+)"\s+';
        $pattern .= 'data-dauersec="(?<dauersec>\d+)"/sU';
        $matchCount = preg_match($pattern, $response, $matches);
        if ($matchCount !== 1) {
            throw new TimebutlerException('Could not parese clock-state from response.');
        }

        $state['clock_state'] = (object) [
            'state' => (int) $matches['state'],
            'running' => (int) $matches['running'],
            'paused' => (int) $matches['paused'],
            'pausesec' => (int) $matches['pausesec'],
            'dauersec' => (int) $matches['dauersec'],
        ];

        return $state;
    }

    /**
     * Sends login request to timebutler.
     *
     * @param string $email
     * @param string $password
     * @return bool
     * @throws TimebutlerException
     */
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
            throw new TimebutlerException('Unexpected response during timebutler http request.');
        }

        // check if logout link is present to be sure we are logged in
        if (strpos($response, 'do?ha=login&ac=2') === false) {
            return false;
        }

        return true;
    }

    /**
     * Sends GET request with given parameters to timebutler.
     *
     * @param array $requestParams
     * @param string $email
     * @return string
     * @throws TimebutlerException
     */
    private function doGetRequest(array $requestParams, string $email): string
    {
        $cookieFile = $this->tmpDir . '/' . sha1($email) . '.txt';
        $url = 'https://timebutler.de/do';
        if (!empty($requestParams)) {
            $url .= '?' . http_build_query($requestParams);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_REFERER, 'https://timebutler.de/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status !== 200) {
            throw new TimebutlerException('Unexpected response during timebutler http request.');
        }

        return $response;
    }

    /**
     * Generates time-string for usage in timebutler requests.
     *
     * @return string
     */
    private function getTimeString(): string
    {
        $time = (string) time();
        $time .= (string) rand(100, 999);

        return $time;
    }

    /**
     * Parses stop-state from a stopclock-action-response.
     *
     * @param string $response
     * @return \stdClass
     * @throws TimebutlerException
     */
    private function parseClockResponse(string $response): \stdClass
    {
        $responseData = json_decode($response);
        if ($responseData->result[0] !== 0) {
            throw new TimebutlerException('Result code in start-response is expected to be 0');
        }
        $responsePayload = $responseData->payload[0];

        return (object) [
            'state' => $responsePayload->state,
            'running' => $responsePayload->running,
            'paused' => $responsePayload->paused,
            'pausesec' => $responsePayload->pausesec,
            'dauersec' => $responsePayload->dauersec,
        ];
    }

    /**
     * Sends login-request to timebutler if user is not currently logged in.
     *
     * @param string $email
     * @param string $password
     * @return bool
     * @throws TimebutlerException
     */
    private function loginIfRequired(string $email, string $password): bool
    {
        $state = $this->getState($email);
        if ($state['logged_in'] === true) {
            return true;
        }

        $loginResult = $this->login($email, $password);
        if ($loginResult === false) {
            throw new TimebutlerException('Could not login to timebutler. Check credentials.');
        }

        return true;
    }
}
