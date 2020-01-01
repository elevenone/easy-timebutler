<?php

declare(strict_types=1);

namespace Nekudo\EasyTimebutler\Actions\Xhr;

use Bloatless\Endocore\Action\JsonAction;
use Bloatless\Endocore\Components\Logger\LoggerInterface;
use Bloatless\Endocore\Http\Request;
use Bloatless\Endocore\Http\Response;
use Nekudo\EasyTimebutler\Domains\AuthDomain;
use Nekudo\EasyTimebutler\Responder\LoginResponder;

/**
 * @property LoginResponder $responder
 */
class LoginAction extends JsonAction
{
    public function __construct(array $config, LoggerInterface $logger, Request $request)
    {
        parent::__construct($config, $logger, $request);
        $this->setResponder(new LoginResponder($this->config));
    }

    public function __invoke(array $arguments = []): Response
    {
        $email = (string) $this->request->getParam('email', '');
        $password = (string) $this->request->getParam('password', '');
        $domain = new AuthDomain($this->config, $this->logger);
        $payload = $domain->handleLogin($email, $password);

        return $this->responder->__invoke($payload);
    }
}
