<?php

declare (strict_types = 1);

namespace Bingo\Page;

/**
 * Represents the handler for the error page.
 */
class ErrorPage extends Page
{
    /**
     * @inheritDoc
     */
    protected function run(array $params): void
    {
        $this->showTemplate('error', $params);
    }
}
