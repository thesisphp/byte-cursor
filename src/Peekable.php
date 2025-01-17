<?php

declare(strict_types=1);

namespace Thesis\Cursor;

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
