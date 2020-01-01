<?php

declare(strict_types=1);

namespace Nekudo\EasyTimebutler\Domains;

use Bloatless\Endocore\Components\Logger\LoggerInterface;

abstract class Domain
{
    /**
     * @var array $config
     */
    protected $config;

    /**
     * @var LoggerInterface $logger
     */
    protected $logger;

    public function __construct(array $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }
}
