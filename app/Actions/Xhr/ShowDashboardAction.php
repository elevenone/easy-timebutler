<?php

declare(strict_types=1);

namespace Nekudo\EasyTimebutler\Actions\Xhr;

use Bloatless\Endocore\Action\JsonAction;
use Bloatless\Endocore\Http\Response;

class ShowDashboardAction extends JsonAction
{
    public function __invoke(array $arguments = []): Response
    {
        $content = file_get_contents(__DIR__ . '/../../../views/dashboard.phtml');
        return $this->responder->found(['content' => $content]);
    }
}
