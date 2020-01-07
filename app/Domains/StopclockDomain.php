<?php

declare(strict_types=1);

namespace Nekudo\EasyTimebutler\Domains;

use Bloatless\Endocore\Components\Logger\LoggerInterface;
use Nekudo\EasyTimebutler\Services\Timebutler\Timebutler;
use Nekudo\EasyTimebutler\Services\Timebutler\TimebutlerException;
use Nekudo\EasyTimebutler\Services\Timebutler\TimebutlerFactory;

class StopclockDomain extends Domain
{
    /**
     * @var AuthDomain $authDomain
     */
    protected $authDomain;

    /**
     * @var Timebutler $timebutlerService
     */
    protected $timebutlerService;

    /**
     * @param array $config
     * @param LoggerInterface $logger
     * @throws \Bloatless\Endocore\Components\QueryBuilder\Exception\DatabaseException
     */
    public function __construct(array $config, LoggerInterface $logger)
    {
        parent::__construct($config, $logger);
        $this->authDomain = new AuthDomain($this->config, $this->logger);
        $this->timebutlerService = (new TimebutlerFactory($this->config, $this->logger))->make();
    }

    /**
     * Requests start of stopclock at timebutler.
     *
     * @param string $token
     * @return Payload
     */
    public function startClock(string $token): Payload
    {
        return $this->invokeStopclockAction('start', $token);
    }

    /**
     * Requests stop of stopclock at timebutler.
     *
     * @param string $token
     * @return Payload
     */
    public function stopClock(string $token): Payload
    {
       return $this->invokeStopclockAction('stop', $token);
    }

    /**
     * Requests pause of stopclock at timebutler.
     *
     * @param string $token
     * @return Payload
     */
    public function pauseClock(string $token): Payload
    {
        return $this->invokeStopclockAction('pause', $token);
    }

    /**
     * Requests resume of stopclock at timebutler.
     *
     * @param string $token
     * @return Payload
     */
    public function resumeClock(string $token): Payload
    {
        return $this->invokeStopclockAction('resume', $token);
    }

    /**
     * Fetches login and stopclock state from timebutler.
     *
     * @param string $token
     * @return Payload
     * @throws \Bloatless\Endocore\Components\QueryBuilder\Exception\DatabaseException
     */
    public function getTimebutlerState(string $token): Payload
    {
        $credentials = $this->authDomain->getCredentialsByToken($token);
        if (empty($credentials)) {
            return new Payload(Payload::STATUS_ERROR, ['error' => 'Invalid token provided.']);
        }

        try {
            $state = $this->timebutlerService->getState($credentials['email']);
        } catch (TimebutlerException $e) {
            return new Payload(Payload::STATUS_ERROR, ['error' => $e->getMessage()]);
        }

        return new Payload(Payload::STATUS_FOUND, [
            'state' => $state
        ]);
    }

    /**
     * Requests a stopclock action at timebutler.
     *
     * @param string $action
     * @param string $token
     * @return Payload
     * @throws \Bloatless\Endocore\Components\QueryBuilder\Exception\DatabaseException
     */
    private function invokeStopclockAction(string $action, string $token): Payload
    {
        $credentials = $this->authDomain->getCredentialsByToken($token);
        if (empty($credentials)) {
            return new Payload(Payload::STATUS_ERROR, ['error' => 'Invalid token provided.']);
        }

        try {
            switch ($action) {
                case 'start':
                    $clockState = $this->timebutlerService->startClock(
                        $credentials['email'],
                        $credentials['password']
                    );
                    break;
                case 'stop':
                    $clockState = $this->timebutlerService->stopClock(
                        $credentials['email'],
                        $credentials['password']
                    );
                    break;
                case 'pause':
                    $clockState = $this->timebutlerService->pauseClock(
                        $credentials['email'],
                        $credentials['password']
                    );
                    break;
                case 'resume':
                    $clockState = $this->timebutlerService->resumeClock(
                        $credentials['email'],
                        $credentials['password']
                    );
                    break;
                default:
                    throw new \RuntimeException('Invlid stopclock action.');
            }

            return new Payload(Payload::STATUS_FOUND, [
                'clock_state' => $clockState
            ]);
        } catch (TimebutlerException $e) {
            return new Payload(Payload::STATUS_ERROR, ['error' => $e->getMessage()]);
        }
    }
}
