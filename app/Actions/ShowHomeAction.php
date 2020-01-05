<?php

declare(strict_types=1);

namespace Nekudo\EasyTimebutler\Actions;

use Bloatless\Endocore\Action\HtmlAction;
use Bloatless\Endocore\Http\Response;

class ShowHomeAction extends HtmlAction
{
    /**
     * Shows index page.
     *
     * @param array $arguments Possible arguments from the URL.
     * @return Response
     */
    public function __invoke(array $arguments = []): Response
    {
        $content = file_get_contents(__DIR__ . '/../../views/index.phtml');

        return $this->responder->found(['body' => $content]);
    }
}
