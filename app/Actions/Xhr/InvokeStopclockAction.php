<?php

declare(strict_types=1);

namespace Nekudo\EasyTimebutler\Actions\Xhr;

use Bloatless\Endocore\Action\JsonAction;
use Bloatless\Endocore\Http\Response;
use Nekudo\EasyTimebutler\Domains\StopclockDomain;

class InvokeStopclockAction extends JsonAction
{
    public function __invoke(array $arguments = []): Response
    {
        $action = (string) $this->request->getParam('action', '');
        $token = (string) $this->request->getParam('token', '');
        $domain = new StopclockDomain($this->config, $this->logger);

        switch ($action) {
            case 'start':
                break;
            case 'pause':
                break;
            case 'resume':
                break;
            case 'stop':
                break;
            default:
                break;
        }

        return $this->responder->found([]);
    }
}
