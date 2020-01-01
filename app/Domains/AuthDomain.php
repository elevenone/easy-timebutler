<?php

declare(strict_types=1);

namespace Nekudo\EasyTimebutler\Domains;

use Bloatless\Endocore\Components\Logger\LoggerInterface;
use Bloatless\Endocore\Components\QueryBuilder\Factory as QueryBuilderFactory;
use Defuse\Crypto\Crypto;
use Nekudo\EasyTimebutler\Services\Timebutler\TimebutlerFactory;

class AuthDomain extends Domain
{
    /**
     * @var QueryBuilderFactory $db
     */
    protected $db;

    /**
     * @var \Nekudo\EasyTimebutler\Services\Timebutler\Timebutler $timebutlerService
     */
    protected $timebutlerService;

    public function __construct(array $config, LoggerInterface $logger)
    {
        parent::__construct($config, $logger);
        $this->db = new QueryBuilderFactory($this->config['db']);
        $timebutlerFactory = new TimebutlerFactory($config, $logger);
        $this->timebutlerService = $timebutlerFactory->make();
    }

    public function handleLogin(string $email, string $password): Payload
    {
        $validationResult = $this->validateCredentials($email, $password);
        if ($validationResult === false) {
            return new Payload(Payload::STATUS_NOT_VALID, [
                'error' => 'Invalid credentials. Please check your input.'
            ]);
        }

        $loginResult = $this->timebutlerService->login($email, $password);
        if ($loginResult === false) {
            return new Payload(Payload::STATUS_NOT_VALID, [
                'error' => 'Invalid credentials. Login denied by Timebutler.'
            ]);
        }

        $user = $this->getUserdataByEmail($email);
        if (empty($user)) {
            $user = $this->createUserFromCredentials($email, $password);
        }

        return new Payload(Payload::STATUS_FOUND, [
            'user' => $user,
        ]);
    }

    private function validateCredentials(string $email, string $password): bool
    {
        $email = trim($email);
        $password = trim($password);

        if (empty($email) || empty($password)) {
            return false;
        }

        return true;
    }

    private function getUserdataByEmail(string $email): ?\stdClass
    {
        return $this->db->makeSelect()
            ->from('users')
            ->whereEquals('email', $email)
            ->first();
    }

    private function createUserFromCredentials(string $email, string $password): \stdClass
    {
        $userdata = [
            'email' => $email,
            'password' => $this->encryptUserPassword($password),
            'token' => $this->provideRandomString(40),
        ];

        $userId = $this->db->makeInsert()
            ->into('users')
            ->row($userdata);
        $userdata['user_id'] = $userId;

        return (object) $userdata;
    }

    private function encryptUserPassword(string $password): string
    {
        if (empty($this->config['app']['enc_key'])) {
            throw new \RuntimeException('Encryption key not set or empty. Check config file.');
        }
        return Crypto::encryptWithPassword($password, $this->config['app']['enc_key']);
    }

    private function decryptUserPassword(string $password): string
    {
        if (empty($this->config['app']['enc_key'])) {
            throw new \RuntimeException('Encryption key not set or empty. Check config file.');
        }
        return Crypto::decryptWithPassword($password, $this->config['app']['enc_key']);
    }

    private function provideRandomString(int $length = 40): string
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }
}
