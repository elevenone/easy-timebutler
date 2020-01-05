<?php

declare(strict_types=1);

namespace Nekudo\EasyTimebutler\Actions\Xhr;

use Bloatless\Endocore\Action\JsonAction;
use Bloatless\Endocore\Components\Logger\LoggerInterface;
use Bloatless\Endocore\Http\Request;
use Bloatless\Endocore\Http\Response;
use Nekudo\EasyTimebutler\Domains\Payload;
use Nekudo\EasyTimebutler\Domains\StopclockDomain;
use Nekudo\EasyTimebutler\Responder\StopclockActionResponder;

/**
 * @property StopclockActionResponder $responder
 */
class InvokeStopclockAction extends JsonAction
{
    public function __construct(array $config, LoggerInterface $logger, Request $request)
    {
        parent::__construct($config, $logger, $request);
        $this->setResponder(new StopclockActionResponder($this->config));
    }

    /**
     * Requests a stopclock action at timebutler and returns new stopclock state.
     *
     * @param array $arguments
     * @return Response
     */
    public function __invoke(array $arguments = []): Response
    {
        $action = (string) $this->request->getParam('action', '');
        $token = (string) $this->request->getParam('token', '');
        $domain = new StopclockDomain($this->config, $this->logger);

        switch ($action) {
            case 'start':
                $payload = $domain->startClock($token);
                break;
            case 'pause':
                $payload = $domain->pauseClock($token);
                break;
            case 'resume':
                $payload = $domain->resumeClock($token);
                break;
            case 'stop':
                $payload = $domain->stopClock($token);
                break;
            default:
                $payload = new Payload(Payload::STATUS_ERROR, ['error' => 'Invalid action requested.']);
                break;
        }

        return $this->responder->__invoke($payload);
    }
}
