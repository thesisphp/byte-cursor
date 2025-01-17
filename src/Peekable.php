<?php

declare(strict_types=1);

namespace Thesis\ByteCursor;

/**
 * @api
 */
interface Peekable
{
    /**
     * @param positive-int $limit
     * @return non-empty-string
     */
    public function peek(int $limit): string;
}
